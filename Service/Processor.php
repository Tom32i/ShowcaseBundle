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

    public function rename(string $filepath, string $newName)
    {
        $name = \pathinfo($filepath, PATHINFO_FILENAME);

        if ($newName !== $name) {
            $directory = \pathinfo($filepath, PATHINFO_DIRNAME);
            $extension = \pathinfo($filepath, PATHINFO_EXTENSION);

            rename(
                sprintf('%s/%s', $this->path, $filepath),
                sprintf('%s/%s/%s.%s', $this->path, $directory, $newName, strtolower($extension))
            );

            $this->server->deleteCache($filepath);
        }
    }

    /**
     * Clear cache
     */
    public function clear(string $filepath)
    {
        $this->server->deleteCache($filepath);
    }

    /**
     * Warmup cache
     */
    public function warmup(string $filepath, string $preset)
    {
        $this->server->makeImage($filepath, ['p' => $preset]);
    }
}
