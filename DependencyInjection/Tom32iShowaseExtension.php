<?php

namespace Tom32i\ShowcaseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\Service\Processor;
use Tom32i\ShowcaseBundle\Twig\TwigExtension;

class Tom32iShowcaseExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $path = preg_replace('#^(.*)/?#', '$1', $config['path']);
        $cache = preg_replace('#^(.*)/?#', '$1', $config['cache']);

        $definition = $container->getDefinition(Browser::class);
        $definition->replaceArgument(0, $path);

        $definition = $container->getDefinition(Processor::class);
        $definition->replaceArgument(1, $path);
        $definition->replaceArgument(2, $cache);
        $definition->replaceArgument(3, $config['presets']);

        $definition = $container->getDefinition(TwigExtension::class);
        $definition->replaceArgument(1, $config['presets']);
    }
}