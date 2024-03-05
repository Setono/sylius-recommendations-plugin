<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Similarity;

/**
 * @internal
 */
final class SimilarityCalculationResult
{
    private int $index = 0;

    /** @var array<int, SimilarityCalculation> */
    private array $calculations = [];

    public function __construct(private readonly int $max, private float $similarityThreshold = 0.0)
    {
    }

    public function add(SimilarityCalculation $calculation): void
    {
        if ($calculation->similarity <= $this->similarityThreshold) {
            return;
        }

        $this->calculations[$this->index++] = $calculation;

        if ($this->index >= 2 * $this->max) {
            $this->sort();
        }
    }

    /**
     * @return array<int, SimilarityCalculation>
     */
    public function getResult(): array
    {
        $this->sort();

        return $this->calculations;
    }

    private function sort(): void
    {
        usort(
            $this->calculations,
            static fn (SimilarityCalculation $a, SimilarityCalculation $b) => $b->similarity <=> $a->similarity,
        );

        if (isset($this->calculations[$this->max])) {
            $this->similarityThreshold = $this->calculations[$this->max]->similarity;
        }

        $this->calculations = array_slice($this->calculations, 0, $this->max);
        $this->index = count($this->calculations);
    }
}
