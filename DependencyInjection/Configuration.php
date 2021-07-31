<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('tom32i_showcase');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('path')
                    ->defaultValue('%kernel.project_dir%/var/showcase')
                ->end()
                ->scalarNode('cache')
                    ->defaultValue('%kernel.project_dir%/var/cache/showcase')
                ->end()
                ->arrayNode('presets')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('w')->end()
                            ->scalarNode('h')->end()
                            ->scalarNode('dpr')->end()
                            ->scalarNode('fit')->end()
                            ->scalarNode('fm')->end()
                        ->end()
                    ->end()
                    ->defaultValue([
                        'thumbnail' => ['w' => 720, 'h' => 480, 'dpr' => 1, 'fit' => 'crop'],
                        'full' => ['w' => 1920, 'h' => 1280, 'dpr' => 1, 'fit' => 'crop'],
                    ])
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
