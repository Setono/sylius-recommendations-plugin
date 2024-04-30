<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Provider;

use Psr\Cache\CacheItemInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\CallbackInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Webmozart\Assert\Assert;

final class CachingRecommendationsProvider implements RecommendationsProviderInterface, CallbackInterface
{
    private readonly bool $taggable;

    public function __construct(
        private readonly RecommendationsProviderInterface $decorated,
        private readonly CacheInterface $cache,
        private readonly int $ttl,
        private readonly string $cacheKeyPrefix = 'frequently_bought_together',
        /** @var non-empty-string $cacheKeySeparator */
        private readonly string $cacheKeySeparator = '---',
        private readonly string $cacheTag = 'frequently_bought_together',
    ) {
        Assert::notContains($this->cacheKeyPrefix, '---', 'The cache key prefix cannot contain the cache key separator');

        $this->taggable = $cache instanceof TagAwareCacheInterface;
    }

    public function getFrequentlyBoughtTogether(ProductVariantInterface|int $productVariant, int $max = 10): array
    {
        if ($productVariant instanceof ProductVariantInterface) {
            $productVariant = (int) $productVariant->getId();
        }

        $recommendations = $this->cache->get(
            key: $this->encodeCacheKey($productVariant, $max),
            callback: $this,
        );

        Assert::isList($recommendations);
        Assert::allIsInstanceOf($recommendations, Recommendation::class);

        return $recommendations;
    }

    /**
     * @param ItemInterface|CacheItemInterface $item
     */
    public function __invoke(CacheItemInterface $item, bool &$save): array
    {
        Assert::isInstanceOf($item, ItemInterface::class);

        $item->expiresAfter($this->ttl);

        if ($this->taggable) {
            $item->tag($this->cacheTag);
        }

        ['productVariant' => $productVariant, 'max' => $max] = $this->decodeCacheKey($item->getKey());

        return $this->decorated->getFrequentlyBoughtTogether($productVariant, $max);
    }

    private function encodeCacheKey(int $productVariant, int $max): string
    {
        return sprintf(
            '%s%s%d%s%d',
            $this->cacheKeyPrefix,
            $this->cacheKeySeparator,
            $productVariant,
            $this->cacheKeySeparator,
            $max,
        );
    }

    /**
     * @return array{productVariant: int, max: int}
     */
    private function decodeCacheKey(string $cacheKey): array
    {
        [, $productVariant, $max] = explode($this->cacheKeySeparator, $cacheKey);
        Assert::integerish($productVariant);
        Assert::integerish($max);

        return [
            'productVariant' => (int) $productVariant,
            'max' => (int) $max,
        ];
    }
}
