<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Twig;

use Setono\SyliusRecommendationsPlugin\Provider\Recommendation;
use Setono\SyliusRecommendationsPlugin\Provider\RecommendationsProviderInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Twig\Extension\RuntimeExtensionInterface;

final class RecommendationsRuntime implements RuntimeExtensionInterface
{
    public function __construct(private readonly RecommendationsProviderInterface $recommendationsProvider)
    {
    }

    /**
     * @return list<Recommendation>
     */
    public function getFrequentlyBoughtTogether(ProductVariantInterface $productVariant, int $max = 10): array
    {
        return $this->recommendationsProvider->getFrequentlyBoughtTogether($productVariant, $max);
    }
}
