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
     * Serve an file
     */
    public function serveFile(string $filepath): BinaryFileResponse
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
        $data = pathinfo($filepath);

        if (!\array_key_exists('dirname', $data)) {
            throw new \Exception("Could not resolve directory name on \"$this->path\".");
        }

        if (!\array_key_exists('extension', $data)) {
            throw new \Exception("Could not resolve file extension on \"$this->path\".");
        }

        $finder = (new Finder())
            ->in(sprintf('%s/%s', $this->path, $data['dirname']))
            ->files()
            ->name($data['filename'] . '.*');

        foreach ($finder as $file) {
            if ($data['extension'] !== $file->getExtension()) {
                /* @phpstan-ignore-next-line */
                return preg_replace(
                    sprintf('#%s$#', $data['extension']),
                    $file->getExtension(),
                    $filepath
                );
            }

            return $filepath;
        }

        throw new \Exception('File not found.');
    }
}
