<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Matrix;

use Webmozart\Assert\Assert;

final class OrderProductMatrix
{
    /**
     * This holds an index of all the products that have been ordered where the key is the product id
     *
     * @var array<int, true>
     */
    private array $products = [];

    /**
     * This holds a list orders where each order is a list of product ids
     *
     * @var list<list<int>>
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

        $this->orders[] = $order;
    }

    /**
     * @return list<array{0: int, 1: float}>
     */
    public function getSimilarProducts(int $targetProduct, int $max): array
    {
        $vectors = [];
        foreach ($this->orders as $row) {
            foreach ($this->products as $product => $_) {
                if (!isset($vectors[$product])) {
                    $vectors[$product] = [];
                }

                $vectors[$product][] = in_array($product, $row) ? 1 : 0;
            }
        }

        /**
         * An array where index 0 is the product id and index 1 is the similarity score
         *
         * @var list<array{0: int, 1: float}> $similarProducts
         */
        $similarProducts = [];

        foreach ($vectors as $product => $vector) {
            if ($product === $targetProduct) {
                continue;
            }

            if (!isset($vectors[$targetProduct])) {
                continue;
            }

            $similarity = $this->cosineSimilarity($vectors[$targetProduct], $vector);
            if ($similarity <= 0) {
                continue;
            }

            $similarProducts[] = [$product, $similarity];
        }

        if ([] === $similarProducts) {
            return [];
        }

        usort($similarProducts, static function (array $a, array $b): int {
            return $b[1] <=> $a[1];
        });

        return array_slice($similarProducts, 0, $max);
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
