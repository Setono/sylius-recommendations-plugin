<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Matrix;

use Setono\SyliusRecommendationsPlugin\Similarity\SimilarityCalculation;
use Setono\SyliusRecommendationsPlugin\Similarity\SimilarityCalculationResult;

final class OrderProductMatrix
{
    /**
     * This holds an index of all the products that have been ordered where the key is the product id
     *
     * @var array<int, true>
     */
    private array $products = [];

    /**
     * This holds a list of orders where each order is a hashmap of product ids where the key is the product id
     *
     * @var list<array<int, true>>
     */
    private array $orders = [];

    /**
     * @param list<int> $order A list of product ids in the order
     */
    public function addOrder(array $order): void
    {
        foreach ($order as $product) {
            $this->products[$product] = true;
        }

        $this->orders[] = array_fill_keys($order, true);
    }

    public function getSimilarProducts(int $targetProduct, int $max): SimilarityCalculationResult
    {
        /** @var array<int, array<int, 1>> $vectors */
        $vectors = [];
        foreach ($this->products as $product => $_) {
            foreach ($this->orders as $idx => $row) {
                if (isset($row[$product])) {
                    $vectors[$product][$idx] = 1;
                }
            }
        }

        $result = new SimilarityCalculationResult($max);

        if (!isset($vectors[$targetProduct])) {
            return $result;
        }

        foreach ($vectors as $product => $vector) {
            if ($product === $targetProduct) {
                continue;
            }

            $result->add(new SimilarityCalculation($product, self::cosineSimilarity($vectors[$targetProduct], $vector)));
        }

        return $result;
    }

    /**
     * @internal This is only public for testing purposes
     */
    public function render(): void
    {
        $products = array_keys($this->products);
        sort($products);

        echo "\n";

        foreach ($products as $product) {
            echo $product . "\t";
        }

        echo "\n";
        foreach ($this->orders as $row) {
            foreach ($products as $product) {
                echo (int) in_array($product, $row) . "\t";
            }
            echo "\n";
        }
        echo "\n";
    }

    /**
     * Here's a calculator to show how the cosine similarity is calculated: https://www.omnicalculator.com/math/cosine-similarity
     *
     * The formula is (a·b) / (‖a‖ × ‖b‖)
     *
     * @param array<int, 1> $vector1
     * @param array<int, 1> $vector2
     */
    private static function cosineSimilarity(array $vector1, array $vector2): float
    {
        $dotProduct = 0;
        $magnitudeVector1 = 0;
        $magnitudeVector2 = 0;

        foreach ($vector1 as $i => $_) {
            ++$magnitudeVector1; // all the values in $vector1 are 1, so we can just add 1

            $v2 = $vector2[$i] ?? 0;

            // only if $v2 is 1 does it make sense to add to the dot product and magnitude of vector 2
            if (1 === $v2) {
                ++$dotProduct; // all the values in $vector1 are 1, so we can just add the value from $vector2
                ++$magnitudeVector2; // all the values in $vector2 are 1, so we can just add 1
            }

            unset($vector2[$i]);
        }

        $magnitudeVector2 += array_sum($vector2);

        return $dotProduct / (sqrt($magnitudeVector1) * sqrt($magnitudeVector2));
    }
}
