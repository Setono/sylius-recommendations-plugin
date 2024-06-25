<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Provider;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Setono\SyliusRecommendationsPlugin\Generator\CacheKeyGeneratorInterface;
use Setono\SyliusRecommendationsPlugin\Message\Command\ComputeRecommendations;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Webmozart\Assert\Assert;

final class CachedRecommendationsProvider implements RecommendationsProviderInterface, LoggerAwareInterface
{
    private LoggerInterface $logger;

    public function __construct(
        private readonly AdapterInterface&CacheInterface $cache,
        private readonly MessageBusInterface $commandBus,
        private readonly CacheKeyGeneratorInterface $cacheKeyGenerator,
    ) {
        $this->logger = new NullLogger();
    }

    public function getFrequentlyBoughtTogether(ProductVariantInterface|int $productVariant, int $max = 10): RecommendationCollection
    {
        if ($productVariant instanceof ProductVariantInterface) {
            $productVariant = (int) $productVariant->getId();
        }

        $item = $this->cache->getItem($this->cacheKeyGenerator->generate($productVariant, $max));

        try {
            if ($item->isHit()) {
                /** @var RecommendationCollection|CachedRecommendationCollection|mixed $recommendationCollection */
                $recommendationCollection = $item->get();
                Assert::isInstanceOf($recommendationCollection, RecommendationCollection::class);

                if ($recommendationCollection instanceof CachedRecommendationCollection && $recommendationCollection->expiresAt < time()) {
                    $recommendationCollection->expiresAt = time() + 1800; // The message bus has 30 minutes to compute the recommendations
                    $this->cache->save($item);

                    $this->commandBus->dispatch(new ComputeRecommendations($productVariant, $max));
                }

                return $recommendationCollection;
            }
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }

        $collection = new RecommendationCollection();

        $item->expiresAfter(1800); // The message bus has 30 minutes to compute the recommendations
        $item->set($collection);
        $this->cache->save($item);

        $this->commandBus->dispatch(new ComputeRecommendations($productVariant, $max));

        // todo in the configuration of the plugin, allow users to decide whether to return an empty collection or call the recommendation provider that computes the recommendations

        return $collection;
    }

    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }
}
