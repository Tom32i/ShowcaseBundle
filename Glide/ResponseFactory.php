<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Glide;

use League\Flysystem\FilesystemOperator;
use League\Glide\Responses\SymfonyResponseFactory;

class ResponseFactory extends SymfonyResponseFactory
{
    public function create(FilesystemOperator $cache, $path)
    {
        $response = parent::create($cache, $path);

        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->addCacheControlDirective('no-cache');
        $response->setEtag(base64_encode($cache->checksum($path)));

        return $response;
    }
}
