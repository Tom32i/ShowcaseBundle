<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Glide;

use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;
use Tom32i\ShowcaseBundle\Service\PresetManager;

class GlideServerFactory
{
    public function __construct(
        private PresetManager $presetManager,
        private string $path,
        private string $cache
    ) {
    }

    public function __invoke(): Server
    {
        return ServerFactory::create([
            'source' => $this->path,
            'cache' => $this->cache,
            'presets' => $this->presetManager->getConfig(),
            'response' => new SymfonyResponseFactory(),
        ]);
    }
}
