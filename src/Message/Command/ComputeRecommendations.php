<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Message\Command;

use Sylius\Component\Product\Model\ProductVariantInterface;

final class ComputeRecommendations implements CommandInterface
{
    public readonly int $productVariant;

    public function __construct(int|ProductVariantInterface $productVariant, public readonly int $max = 10)
    {
        if ($productVariant instanceof ProductVariantInterface) {
            $productVariant = (int) $productVariant->getId();
        }

        $this->productVariant = $productVariant;
    }
}
