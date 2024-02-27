<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Provider;

use Doctrine\Persistence\ManagerRegistry;
use Setono\DoctrineObjectManagerTrait\ORM\ORMManagerTrait;
use Setono\SyliusRecommendationsPlugin\Matrix\OrderProductMatrix;
use Sylius\Component\Order\Model\OrderItemInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Repository\ProductVariantRepositoryInterface;

final class RecommendationsProvider implements RecommendationsProviderInterface
{
    use ORMManagerTrait;

    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        ManagerRegistry $managerRegistry,
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

        /**
         * We want to build a query like this:
         *
         * SELECT order_id, variant_id FROM sylius_order_item WHERE order_id IN (
         *   SELECT order_id FROM sylius_order_item WHERE variant_id IN (
         *     SELECT variant_id FROM sylius_order_item WHERE order_id IN (
         *       SELECT order_id FROM sylius_order_item WHERE variant_id = 748
         *     )
         *   )
         * )
         *
         * I am not a SQL expert, but we will make it better later
         */
        $expr = $manager->getExpressionBuilder();

        $rows = $manager
            ->createQueryBuilder()
            ->select('IDENTITY(oi1.order) as order_id, IDENTITY(oi1.variant) as variant_id')
            ->from($this->orderItemClass, 'oi1')
            ->where($expr->in(
                'IDENTITY(oi1.order)',
                $manager->createQueryBuilder()
                ->select('IDENTITY(oi2.order)')
                ->from($this->orderItemClass, 'oi2')
                ->where($expr->in(
                    'IDENTITY(oi2.variant)',
                    $manager->createQueryBuilder()
                        ->select('IDENTITY(oi3.variant)')
                        ->from($this->orderItemClass, 'oi3')
                        ->where($expr->in(
                            'IDENTITY(oi3.order)',
                            $manager->createQueryBuilder()
                                ->select('IDENTITY(oi4.order)')
                                ->from($this->orderItemClass, 'oi4')
                                ->andWhere('oi4.variant = :variant')
                                ->getDQL(),
                        ))
                    ->getDQL(),
                ))
                ->getDQL(),
            ))
            ->setParameter('variant', $productVariant)
            ->getQuery()
            ->getArrayResult()
        ;

        $orders = [];

        foreach ($rows as $row) {
            $orders[$row['order_id']][] = $row['variant_id'];
        }

        $matrix = new OrderProductMatrix();

        foreach ($orders as $order) {
            $matrix->addOrder($order);
        }

        $variants = [];
        foreach ($matrix->getSimilarProducts($productVariant->getId(), $max) as $productVariantId) {
            $variants[] = $this->productVariantRepository->find($productVariantId);
        }

        return $variants;
    }
}
