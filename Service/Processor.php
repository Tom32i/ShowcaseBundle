<?php

namespace Tom32i\ShowcaseBundle\Service;

use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RequestStack;

class Processor
{
    private RequestStack $requestStack;

    /** Source directory */
    private string $path;

    /** Glide Server */
    private Server $server;

    public function __construct(RequestStack $requestStack, string $path, string $cache, array $presets = [])
    {
        $this->requestStack = $requestStack;
        $this->path = $path;
        $this->server = ServerFactory::create([
            'source' => $path,
            'cache' => $cache,
            'presets' => $presets,
            'response' => new SymfonyResponseFactory(
                $requestStack->getCurrentRequest()
            ),
        ]);
    }

    /**
     * Clear cache
     */
    public function clear(string $filepath)
    {
        $this->server->deleteCache($filepath);
    }

    /**
     * Serve an image with the given preset
     */
    public function serveImage(string $filepath, string $preset): Response
    {
        $this->server->setResponseFactory(
            new SymfonyResponseFactory($this->requestStack->getCurrentRequest())
        );

        return $this->server->getImageResponse($filepath, ['p' => $preset]);
    }

    /**
     * Serve an file
     */
    public function serveFile(string $filepath): Response
    {
        return new BinaryFileResponse(
            sprintf('%s/%s', $this->path, $filepath)
        );
    }
}
