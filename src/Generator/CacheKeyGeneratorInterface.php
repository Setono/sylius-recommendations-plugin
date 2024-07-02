<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Generator;

interface CacheKeyGeneratorInterface
{
    public function generate(int $productVariant, int $max = 10): string;
}
