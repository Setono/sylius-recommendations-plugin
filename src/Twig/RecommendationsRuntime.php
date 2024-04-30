<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Twig;

use Setono\SyliusRecommendationsPlugin\Provider\Recommendation;
use Setono\SyliusRecommendationsPlugin\Provider\RecommendationsProviderInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Repository\ProductVariantRepositoryInterface;
use Twig\Extension\RuntimeExtensionInterface;
use Webmozart\Assert\Assert;

final class RecommendationsRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RecommendationsProviderInterface $recommendationsProvider,
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
    ) {
    }

    /**
     * @return list<Recommendation>
     */
    public function getFrequentlyBoughtTogether(ProductVariantInterface $productVariant, int $max = 10): array
    {
        return $this->recommendationsProvider->getFrequentlyBoughtTogether($productVariant, $max);
    }

    public function getProductVariant(int $id): ?ProductVariantInterface
    {
        $obj = $this->productVariantRepository->find($id);
        Assert::nullOrIsInstanceOf($obj, ProductVariantInterface::class);

        return $obj;
    }
}
