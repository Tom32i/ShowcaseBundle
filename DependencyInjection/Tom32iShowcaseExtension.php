<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Tom32i\ShowcaseBundle\Behavior\Properties;
use Tom32i\ShowcaseBundle\Service\CachedPropertyManager;
use Tom32i\ShowcaseBundle\Service\NullPropertyManager;

class Tom32iShowcaseExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $path = preg_replace('#^(.*)/?#', '$1', $config['path']);
        $cache = preg_replace('#^(.*)/?#', '$1', $config['cache']);

        // Register config as parameters
        $container->setParameter('tom32i_showcase.path', $config['path']);
        $container->setParameter('tom32i_showcase.cache', $config['cache']);
        $container->setParameter('tom32i_showcase.presets', $config['presets']);
        $container->setParameter('tom32i_showcase.group_class', $config['group_class']);
        $container->setParameter('tom32i_showcase.image_class', $config['image_class']);

        $container->setAlias(Properties::class, $this->getPropertyManager());
    }

    public function getPropertyManager(): string
    {
        if (\extension_loaded('imagick')) {
            return CachedPropertyManager::class;
        }

        return NullPropertyManager::class;
    }
}
