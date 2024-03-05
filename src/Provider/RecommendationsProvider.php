<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Provider;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\SyliusRecommendationsPlugin\Matrix\OrderProductMatrix;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Repository\ProductVariantRepositoryInterface;
use Webmozart\Assert\Assert;

final class RecommendationsProvider implements RecommendationsProviderInterface
{
    use ORMManagerTrait;

    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        ManagerRegistry $managerRegistry,
        /**
         * @var class-string<OrderInterface> $orderClass
         */
        private readonly string $orderClass,
        /**
         * @var class-string<OrderItemInterface> $orderItemClass
         */
        private readonly string $orderItemClass,
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    public function getFrequentlyBoughtTogether(ProductVariantInterface $productVariant, int $max = 10): array
    {
        $manager = $this->getManager($this->orderItemClass);
        $classMetadata = $manager->getClassMetadata($this->orderItemClass);

        $orderColumn = self::getAssociationColumn($classMetadata, 'order');
        Assert::notNull($orderColumn);

        $variantColumn = self::getAssociationColumn($classMetadata, 'variant');
        Assert::notNull($variantColumn);

        $sql = <<<SQL
        SELECT %order_id%, %variant_id% FROM %table% WHERE %order_id% IN (
          SELECT %order_id% FROM %table% WHERE %order_id% > :order_threshold AND %variant_id% IN (
            SELECT %variant_id% FROM %table% WHERE %order_id% IN (
              SELECT %order_id% FROM %table% WHERE %variant_id% = :variant_id AND %order_id% > :order_threshold
            )
          )
        )
SQL;

        /** @var list<array{order_id: int, variant_id: int}> $rows */
        $rows = $manager
            ->getConnection()
            ->prepare(str_replace(
                ['%order_id%', '%variant_id%', '%table%'],
                [$orderColumn, $variantColumn, $classMetadata->getTableName()],
                $sql,
            ))
            ->executeQuery([
                'variant_id' => $productVariant->getId(),
                'order_threshold' => $this->getOrderThreshold(),
            ])
            ->fetchAllAssociative()
        ;

        /** @var array<int, list<int>> $orders */
        $orders = [];

        foreach ($rows as $key => $row) {
            $orders[$row[$orderColumn]][] = $row[$variantColumn];
            unset($rows[$key]);
        }
        unset($rows);

        $matrix = new OrderProductMatrix();

        foreach ($orders as $key => $order) {
            $matrix->addOrder($order);
            unset($orders[$key]);
        }
        unset($orders);

        $recommendations = [];
        foreach ($matrix->getSimilarProducts((int) $productVariant->getId(), $max)->getResult() as $result) {
            $variantResult = $this->productVariantRepository->find($result->subject);
            if (!$variantResult instanceof ProductVariantInterface) {
                // todo this should be impossible. Should we throw an exception or log it?
                continue;
            }

            $recommendations[] = new Recommendation($variantResult, $result->similarity);
        }

        return $recommendations;
    }

    private function getOrderThreshold(): int
    {
        return (int) $this->getManager($this->orderClass)
            ->createQueryBuilder()
            ->select('o.id')
            ->from($this->orderClass, 'o')
            ->andWhere('o.createdAt > :date')
            ->addOrderBy('o.id', 'ASC')
            ->setParameter('date', new \DateTimeImmutable('-180 day')) // todo should be configurable
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    private static function getAssociationColumn(ClassMetadata $classMetadata, string $column): ?string
    {
        $associationMappings = $classMetadata->getAssociationMappings();

        return $associationMappings[$column]['joinColumns'][0]['name'] ?? null;
    }
}
