<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Provider;

use Sylius\Component\Product\Model\ProductVariantInterface;

interface RecommendationsProviderInterface
{
    /**
     * @param int $max The maximum number of recommendations to return
     *
     * @return list<ProductVariantInterface>
     */
    public function getFrequentlyBoughtTogether(ProductVariantInterface $productVariant, int $max = 10): array;
}
