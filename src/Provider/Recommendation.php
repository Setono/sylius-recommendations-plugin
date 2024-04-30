<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Provider;

final class Recommendation
{
    public function __construct(
        /** The id of the recommended item */
        public readonly int $id,
        public readonly float $similarity,
        /** This is true if the recommendation is a fallback recommendation */
        public readonly bool $fallback = false,
    ) {
    }
}
