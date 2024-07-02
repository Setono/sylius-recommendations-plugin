<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Provider;

use Traversable;

/**
 * @implements \IteratorAggregate<int, Recommendation>
 */
class RecommendationCollection implements \IteratorAggregate, \Countable
{
    public function __construct(
        /** @var list<Recommendation> $recommendations */
        private readonly array $recommendations = [],
    ) {
    }

    /**
     * @return list<Recommendation>
     */
    public function toArray(): array
    {
        return $this->recommendations;
    }

    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->recommendations);
    }

    public function count(): int
    {
        return count($this->recommendations);
    }

    public function empty(): bool
    {
        return [] === $this->recommendations;
    }
}
