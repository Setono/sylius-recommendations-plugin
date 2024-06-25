<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public const CACHE = 'setono_sylius_recommendations.cache';

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('setono_sylius_recommendations');
        $rootNode = $treeBuilder->getRootNode();

        /** @psalm-suppress MixedMethodCall,UndefinedMethod,PossiblyUndefinedMethod,PossiblyNullReference */
        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pool')
                            ->info('The cache pool to use for caching recommendations')
                            ->defaultValue(self::CACHE)
                            ->cannotBeEmpty()
        ;

        return $treeBuilder;
    }
}
