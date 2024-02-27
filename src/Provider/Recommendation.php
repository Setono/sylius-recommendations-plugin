<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Provider;

use Sylius\Component\Product\Model\ProductVariantInterface;

final class Recommendation
{
    public function __construct(
        public readonly ProductVariantInterface $productVariant,
        public readonly float $similarity,
    ) {
    }
}
