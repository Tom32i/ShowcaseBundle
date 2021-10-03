<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

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
    }
}
