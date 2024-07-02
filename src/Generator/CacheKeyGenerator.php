<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Generator;

final class CacheKeyGenerator implements CacheKeyGeneratorInterface
{
    public function __construct(
        private readonly string $prefix,
        private readonly string $separator = '---',
    ) {
    }

    public function generate(int $productVariant, int $max = 10): string
    {
        return sprintf(
            '%s%s%d%s%d',
            $this->prefix,
            $this->separator,
            $productVariant,
            $this->separator,
            $max,
        );
    }
}
