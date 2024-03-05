<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Tests\Similarity;

use PHPUnit\Framework\TestCase;
use Setono\SyliusRecommendationsPlugin\Similarity\SimilarityCalculation;
use Setono\SyliusRecommendationsPlugin\Similarity\SimilarityCalculationResult;

final class SimilarityCalculationResultTest extends TestCase
{
    /**
     * @test
     */
    public function it_returns_a_maximum_number_of_results(): void
    {
        $result = new SimilarityCalculationResult(3);
        $result->add($expected3 = new SimilarityCalculation(1, 0.5));
        $result->add(new SimilarityCalculation(2, 0.4));
        $result->add($expected2 = new SimilarityCalculation(3, 0.6));
        $result->add(new SimilarityCalculation(4, 0.2));
        $result->add($expected1 = new SimilarityCalculation(5, 0.8));

        self::assertCount(3, $result->getResult());
        self::assertSame([$expected1, $expected2, $expected3], $result->getResult());
    }
}
