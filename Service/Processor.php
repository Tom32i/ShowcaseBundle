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
     *
     * @param string $filename
     */
    public function clear($filename)
    {
        $this->server->deleteCache($filename);
    }

    /**
     * Process file with the given process
     *
     * @param string $filename File name
     * @param string|null $preset Preset name
     *
     * @return string Full file path
     */
    public function process(string $filename, string $preset = null): ?string
    {
        $path = sprintf('%s/%s', $this->source, $filename);
        dump($path);
        if (!file_exists($path)) {
            return null;
        }

        if (!$preset) {
            return $path;
        }

        $file = $this->server->makeImage($filename, ['p' => $preset]);

        return sprintf('%s/%s', $this->getCacheDirectory(), $file);
    }

    public function serveImage(string $filepath, string $preset): Response
    {
        $this->server->setResponseFactory(
            new SymfonyResponseFactory($this->requestStack->getCurrentRequest())
        );

        return $this->server->getImageResponse($filepath, ['p' => $preset]);

        /*if (!$filepath) {
            return new Response('File not found.', Response::HTTP_NOT_FOUND);
        }

        $response = new BinaryFileResponse(
            $filepath,
            200,  // status
            [
                'expires' => $days . 'd',
                'max_age' => $days * 24 * 60 * 60,
            ],
            true, // public
            null, // contentDisposition
            true, // autoEtag
            true  // autoLastModified
        );

        $response->isNotModified($request);

        return $response;*/
    }

    public function serveFile(string $filepath): Response
    {
        return new BinaryFileResponse($filepath);
    }
}
