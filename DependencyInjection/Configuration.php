<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Tom32i\ShowcaseBundle\Model\Group;
use Tom32i\ShowcaseBundle\Model\Image;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
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
                ->scalarNode('group_class')
                    ->defaultValue(Group::class)
                    ->validate()
                        ->ifTrue(fn (string $value) => !class_exists($value))
                        ->thenInvalid('Group class not found')
                    ->end()
                    ->validate()
                        ->ifTrue(fn (string $value) => !is_a($value, Group::class, true))
                        ->thenInvalid('Not a valid Group class')
                    ->end()
                ->end()
                ->scalarNode('image_class')
                    ->defaultValue(Image::class)
                    ->validate()
                        ->ifTrue(fn (string $value) => !class_exists($value))
                        ->thenInvalid('Image class not found')
                    ->end()
                    ->validate()
                        ->ifTrue(fn (string $value) => !is_a($value, Image::class, true))
                        ->thenInvalid('Not a valid Image class')
                    ->end()
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
