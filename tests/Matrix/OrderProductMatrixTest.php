<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Tests\Matrix;

use PHPUnit\Framework\TestCase;
use Setono\SyliusRecommendationsPlugin\Matrix\OrderProductMatrix;

final class OrderProductMatrixTest extends TestCase
{
    /**
     * @test
     */
    public function it_adds_orders(): void
    {
        $matrix = new OrderProductMatrix();

        /**
         * Produces a matrix like this:
         *
         * 100	101	102	103	104	105
         * 1	1	1	0	0	0
         * 1	0	1	1	0	0
         * 0	0	0	1	0	0
         * 0	0	0	1	0	0
         * 0	0	1	1	1	0
         * 0	0	0	0	0	1
         */
        $matrix->addOrder([100, 101, 102]);
        $matrix->addOrder([100, 102, 103]);
        $matrix->addOrder([103]);
        $matrix->addOrder([103]); // we add product 103 twice without the product 102 to test that the similarity is then lowered on product 103
        $matrix->addOrder([102, 103, 104]);
        $matrix->addOrder([105]); // product 105 should have a similarity of 0 with product 102 because it has never been bought together

        self::assertSame([
            [100, 0.8164965809277259],
            [101, 0.5773502691896258],
            [103, 0.5773502691896258],
            [104, 0.5773502691896258],
        ], $matrix->getSimilarProducts(102, 10));
    }
}
