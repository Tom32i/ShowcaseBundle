<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use League\Glide\Responses\SymfonyResponseFactory;
use League\Glide\Server;
use League\Glide\ServerFactory;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class Processor
{
    /** Source directory */
    private string $path;

    /** Glide Server */
    private Server $server;

    public function __construct(string $path, string $cache, array $presets = [])
    {
        $this->path = $path;
        $this->server = ServerFactory::create([
            'source' => $path,
            'cache' => $cache,
            'presets' => $presets,
            'response' => new SymfonyResponseFactory(),
        ]);
    }

    /**
     * Serve an image with the given preset
     */
    public function serveImage(string $filepath, string $preset): Response
    {
        return $this->server->getImageResponse(
            $this->getFilePath($filepath),
            ['p' => $preset]
        );
    }

    /**
     * Serve an file
     */
    public function serveFile(string $filepath): Response
    {
        return new BinaryFileResponse(
            sprintf('%s/%s', $this->path, $filepath),
            200,  // status
            [
                'expires' => '30d',
                'max_age' => 30 * 24 * 60 * 60,
            ],
            true, // public
            null, // contentDisposition
            true, // autoEtag
            true  // autoLastModified
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
}
