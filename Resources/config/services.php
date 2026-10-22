<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use League\Glide\Server;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tom32i\ShowcaseBundle\Behavior\Properties;
use Tom32i\ShowcaseBundle\Command\ClearCacheCommand;
use Tom32i\ShowcaseBundle\Command\GenerateCacheCommand;
use Tom32i\ShowcaseBundle\Command\NormalizeNamesCommand;
use Tom32i\ShowcaseBundle\Controller\MediaController;
use Tom32i\ShowcaseBundle\Glide\GlideServerFactory;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\Service\CachedPropertyManager;
use Tom32i\ShowcaseBundle\Service\Normalizer;
use Tom32i\ShowcaseBundle\Service\NullPropertyManager;
use Tom32i\ShowcaseBundle\Service\PresetManager;
use Tom32i\ShowcaseBundle\Service\Processor;
use Tom32i\ShowcaseBundle\Service\PropertyManager;
use Tom32i\ShowcaseBundle\Twig\TwigExtension;
use Tom32i\ShowcaseBundle\ValueResolver\GroupValueResolver;
use Tom32i\ShowcaseBundle\ValueResolver\ImageValueResolver;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set(GlideServerFactory::class)
            ->args([
                service(RequestStack::class),
                service(PresetManager::class),
                param('tom32i_showcase.path'),
                param('tom32i_showcase.cache'),
            ])

        ->set('configured_glide_server', Server::class)
            ->factory(service(GlideServerFactory::class))

        ->set(PropertyAccessor::class)
            ->factory([PropertyAccess::class, 'createPropertyAccessor'])

        ->set(Browser::class)
            ->args([
                service(PropertyAccessor::class),
                service(Properties::class),
                param('tom32i_showcase.path'),
                param('tom32i_showcase.group_class'),
                param('tom32i_showcase.image_class'),
            ])

        ->set(Processor::class)
            ->args([
                service('configured_glide_server'),
                param('tom32i_showcase.path'),
            ])

        ->set(PresetManager::class)
            ->args([
                param('tom32i_showcase.presets'),
            ])

        ->set(Normalizer::class)
            ->args([
                service(Processor::class),
                param('tom32i_showcase.path'),
            ])

        ->set('imagick_cache', FilesystemAdapter::class)
            ->arg('$namespace', 'showcase-imagick')
            ->arg('$defaultLifetime', 0)

        ->set(CachedPropertyManager::class)
            ->decorate(PropertyManager::class, CachedPropertyManager::class . '.inner')
            ->args([
                service(CachedPropertyManager::class . '.inner'),
                service('imagick_cache'),
            ])

        ->set(PropertyManager::class)

        ->set(NullPropertyManager::class)

        ->set(MediaController::class)
            ->args([
                service(Processor::class),
            ])
            ->tag('controller.service_arguments')

        ->set(TwigExtension::class)
            ->args([
                service(UrlGeneratorInterface::class),
                service(PresetManager::class),
            ])
            ->tag('twig.extension')

        ->set(ClearCacheCommand::class)
            ->args([
                service(Browser::class),
                service(Processor::class),
            ])
            ->tag('console.command')

        ->set(GenerateCacheCommand::class)
            ->args([
                service(Browser::class),
                service(Processor::class),
                service(PresetManager::class),
            ])
            ->tag('console.command')

        ->set(NormalizeNamesCommand::class)
            ->args([
                service(Browser::class),
                service(Normalizer::class),
                param('tom32i_showcase.path'),
            ])
            ->tag('console.command')

        ->set(GroupValueResolver::class)
            ->args([
                service(Browser::class),
            ])
            ->tag('controller.argument_value_resolver', ['name' => 'showcase_group', 'priority' => 120])

        ->set(ImageValueResolver::class)
            ->args([
                service(Browser::class),
            ])
            ->tag('controller.argument_value_resolver', ['name' => 'showcase_image', 'priority' => 110])
    ;
};
