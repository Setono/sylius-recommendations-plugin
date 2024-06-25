<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Provider;

final class CachedRecommendationCollection extends RecommendationCollection
{
    /**
     * The timestamp when the collection expires
     */
    public int $expiresAt;

    /**
     * @param list<Recommendation> $recommendations
     */
    public function __construct(
        array $recommendations = [],
        \DateTimeImmutable $expiresAt = new \DateTimeImmutable('+7 days'),
        public readonly int $identicalRecommendationsCount = 0,
    ) {
        parent::__construct($recommendations);

        $this->expiresAt = $expiresAt->getTimestamp();
    }

    public static function fromRecommendationCollection(
        RecommendationCollection $recommendationCollection,
        \DateTimeImmutable $expiresAt = new \DateTimeImmutable('+7 days'),
        int $identicalRecommendationsCount = 0,
    ): self {
        return new self($recommendationCollection->toArray(), $expiresAt, $identicalRecommendationsCount);
    }
}
