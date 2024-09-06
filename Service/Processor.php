<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use League\Glide\Server;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Processor
{
    public function __construct(
        private Server $server,
        private string $path,
    ) {
    }

    /**
     * Serve an image with the given preset
     */
    public function serveImage(string $filepath, string $preset): StreamedResponse
    {
        return $this->server->getImageResponse(
            $this->getFilePath($filepath),
            ['p' => $preset]
        );
    }

    /**
     * Serve a file
     */
    public function serveFile(string $filepath): BinaryFileResponse
    {
        return new BinaryFileResponse(
            file: \sprintf('%s/%s', $this->path, $filepath),
            status: 200,
            headers: [
                'expires' => '30d', // 30 days
                'max_age' => 30 * 24 * 60 * 60, // 30 days
            ],
            public: true,
            autoEtag: true,
            autoLastModified: true,
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
        $directory = pathinfo($filepath, PATHINFO_DIRNAME);
        $filename = pathinfo($filepath, PATHINFO_FILENAME);

        $finder = (new Finder())->in(\sprintf('%s/%s', $this->path, $directory))->files()->name($filename . '.*');

        foreach ($finder as $file) {
            return \sprintf('%s/%s.%s', $directory, $filename, $file->getExtension());
        }

        throw new \Exception('File not found.');
    }
}
