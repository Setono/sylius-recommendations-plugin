<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Similarity;

/**
 * @internal
 */
final class SimilarityCalculation
{
    public function __construct(public readonly int $id, public readonly float $similarity)
    {
    }
}
