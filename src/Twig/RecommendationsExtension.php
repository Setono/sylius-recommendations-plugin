<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class RecommendationsExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('ssr_get_recommendations', [RecommendationsRuntime::class, 'getFrequentlyBoughtTogether']),
            new TwigFunction('ssr_get_product_variant', [RecommendationsRuntime::class, 'getProductVariant']),
        ];
    }
}
