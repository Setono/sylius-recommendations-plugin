<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\EventListener\Doctrine;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\Event\LoadClassMetadataEventArgs;
use Sylius\Component\Order\Model\OrderItemInterface;

/**
 * This listener will add an index (variant_id, order_id) to the order_item table
 */
final class AddIndexListener
{
    public function loadClassMetadata(LoadClassMetadataEventArgs $event): void
    {
        /** @var ClassMetadata $classMetadata */
        $classMetadata = $event->getClassMetadata();
        if (!is_a($classMetadata->getName(), OrderItemInterface::class, true)) {
            return;
        }

        $orderColumn = self::getAssociationColumn($classMetadata, 'order');
        if (null === $orderColumn) {
            return;
        }

        $variantColumn = self::getAssociationColumn($classMetadata, 'variant');
        if (null === $variantColumn) {
            return;
        }

        if (isset($classMetadata->table['indexes'])) {
            /** @var array{columns: list<string>} $index */
            foreach ($classMetadata->table['indexes'] as $index) {
                if ($index['columns'] === [$variantColumn, $orderColumn]) {
                    return;
                }
            }
        }

        $classMetadata->table['indexes'][] = [
            'columns' => [$variantColumn, $orderColumn],
        ];
    }

    private static function getAssociationColumn(ClassMetadata $classMetadata, string $column): ?string
    {
        $associationMappings = $classMetadata->getAssociationMappings();

        return $associationMappings[$column]['joinColumns'][0]['name'] ?? null;
    }
}
