<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use League\Glide\Server;
use League\Glide\ServerFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class Processor
{
    private RequestStack $requestStack;

    /** Source directory */
    private string $path;

    private string $cache;

    /** Glide Server */
    private Server $server;

    public function __construct(RequestStack $requestStack, string $path, string $cache, array $presets = [])
    {
        $this->requestStack = $requestStack;
        $this->path = $path;
        $this->cache = $cache;
        $this->server = ServerFactory::create([
            'source' => $path,
            'cache' => $cache,
            'presets' => $presets,
        ]);
    }

    /**
     * Serve an image with the given preset
     */
    public function serveImage(string $filepath, string $preset): Response
    {
        $cachePath = $this->server->makeImage(
            $this->getFilePath($filepath),
            ['p' => $preset]
        );

        return new BinaryFileResponse(
            sprintf('%s/%s', $this->cache, $cachePath)
        );
    }

    private function getFilePath(string $filepath): string
    {
        $finder = new Finder();
        $data = pathinfo($filepath);

        $finder->in(sprintf('%s/%s', $this->path, $data['dirname']))
            ->files()
            ->name($data['filename'] . '.*');

        foreach ($finder as $file) {
            if ($data['extension'] !== $file->getExtension()) {
                $filepath = preg_replace(sprintf('#%s$#', $data['extension']), $file->getExtension(), $filepath);
            }

            return $filepath;
        }

        throw new \Exception('File not found.');
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

    /**
     * Clear cache
     */
    public function clear(string $filepath): void
    {
        $this->server->deleteCache($filepath);
    }

    /**
     * Warmup cache
     */
    public function warmup(string $filepath, string $preset): void
    {
        $this->server->makeImage($filepath, ['p' => $preset]);
    }
}
