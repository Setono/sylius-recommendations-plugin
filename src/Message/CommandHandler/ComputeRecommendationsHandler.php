<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\Message\CommandHandler;

use Setono\SyliusRecommendationsPlugin\Generator\CacheKeyGeneratorInterface;
use Setono\SyliusRecommendationsPlugin\Message\Command\ComputeRecommendations;
use Setono\SyliusRecommendationsPlugin\Provider\CachedRecommendationCollection;
use Setono\SyliusRecommendationsPlugin\Provider\RecommendationsProviderInterface;
use Sylius\Component\Product\Model\ProductVariantInterface;
use Sylius\Component\Product\Repository\ProductVariantRepositoryInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Contracts\Cache\CacheInterface;

final class ComputeRecommendationsHandler
{
    public function __construct(
        private readonly ProductVariantRepositoryInterface $productVariantRepository,
        private readonly RecommendationsProviderInterface $recommendationsProvider,
        private readonly AdapterInterface&CacheInterface $cache,
        private readonly CacheKeyGeneratorInterface $cacheKeyGenerator,
    ) {
    }

    public function __invoke(ComputeRecommendations $message): void
    {
        $productVariant = $this->productVariantRepository->find($message->productVariant);
        if (!$productVariant instanceof ProductVariantInterface) {
            throw new UnrecoverableMessageHandlingException(sprintf('Product variant with id %d does not exist', $message->productVariant));
        }

        // todo calculate the expiration time based on the number of times the value has been the same
        $expiresAt = new \DateTimeImmutable('+7 days');

        $item = $this->cache->getItem($this->cacheKeyGenerator->generate((int) $productVariant->getId(), $message->max));
        $item->expiresAt($expiresAt->add(new \DateInterval('P7D'))); // todo make this configurable

        $item->set(
            CachedRecommendationCollection::fromRecommendationCollection(
                $this->recommendationsProvider->getFrequentlyBoughtTogether($productVariant, $message->max),
                $expiresAt,
            ),
        );

        $this->cache->save($item);
    }
}
