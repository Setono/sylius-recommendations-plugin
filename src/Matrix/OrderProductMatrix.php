<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Matrix;

use Webmozart\Assert\Assert;

final class OrderProductMatrix
{
    /** @var array<int, true> */
    private array $products = [];

    /** @var list<array<int, 1>> A matrix where each row represents an order and each column represents a product */
    private array $matrix = [];

    /**
     * @param list<int> $order A list of product ids in the order
     */
    public function addOrder(array $order): void
    {
        foreach ($order as $product) {
            $this->products[$product] = true;
        }

        $this->matrix[] = array_fill_keys($order, 1);
    }

    /**
     * @return list<int>
     */
    public function getSimilarProducts(int $targetProduct, int $max): array
    {
        $productVectors = [];
        foreach ($this->matrix as $row) {
            foreach ($this->products as $product => $_) {
                if (!isset($productVectors[$product])) {
                    $productVectors[$product] = [];
                }

                $productVectors[$product][] = $row[$product] ?? 0;
            }
        }

        /**
         * @var array<int, float> $similarProducts An array where the key is the product id and the value is the similarity score
         */
        $similarProducts = [];

        foreach ($productVectors as $product => $vector) {
            if ($product === $targetProduct) {
                continue;
            }

            if (!isset($productVectors[$targetProduct])) {
                continue;
            }

            $similarity = $this->cosineSimilarity($productVectors[$targetProduct], $vector);
            if ($similarity <= 0) {
                continue;
            }

            $similarProducts[$product] = $similarity;
        }

        if ([] === $similarProducts) {
            return [];
        }

        arsort($similarProducts, \SORT_NUMERIC);

        return array_slice(array_keys($similarProducts), 0, $max);
    }

    public function render(): void
    {
        $products = array_keys($this->products);
        sort($products);

        echo "\n";

        foreach ($products as $product) {
            echo $product . "\t";
        }

        echo "\n";
        foreach ($this->matrix as $row) {
            foreach ($products as $product) {
                echo (int) isset($row[$product]) . "\t";
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
     * @param list<0|1> $vector1
     * @param list<0|1> $vector2
     */
    private function cosineSimilarity(array $vector1, array $vector2): float
    {
        // first we check that the vectors are of the same size
        $vector1Count = count($vector1);
        $vector2Count = count($vector2);

        Assert::same($vector1Count, $vector2Count);

        $dotProduct = 0;
        $magnitudeVector1 = 0;
        $magnitudeVector2 = 0;

        for ($i = 0; $i < $vector1Count; ++$i) {
            $dotProduct += $vector1[$i] * $vector2[$i];

            $magnitudeVector1 += $vector1[$i] ** 2;
            $magnitudeVector2 += $vector2[$i] ** 2;
        }

        return $dotProduct / (sqrt($magnitudeVector1) * sqrt($magnitudeVector2));
    }
}
