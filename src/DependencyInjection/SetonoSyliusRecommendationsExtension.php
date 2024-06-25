<?php

declare(strict_types=1);

namespace Setono\SyliusRecommendationsPlugin\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SetonoSyliusRecommendationsExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        /**
         * @psalm-suppress PossiblyNullArgument
         *
         * @var array{
         *      cache: array{ pool: string }
         * } $config
         */
        $config = $this->processConfiguration($this->getConfiguration([], $container), $configs);
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        if (Configuration::CACHE !== $config['cache']['pool']) {
            $container->setAlias(Configuration::CACHE, $config['cache']['pool']);
        }

        $loader->load('services.xml');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'cache' => [
                'pools' => [
                    Configuration::CACHE => [
                        'adapter' => 'cache.app',
                    ],
                ],
            ],
            'messenger' => [
                'buses' => [
                    'setono_sylius_recommendations.command_bus' => null,
                ],
            ],
        ]);
    }
}
