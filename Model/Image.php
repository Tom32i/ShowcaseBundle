<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Model;

class Image extends File
{
    /**
     * @param array<string, mixed> $exif
     */
    public function __construct(
        string $slug,
        string $path,
        \DateTimeImmutable $date,
        private array $exif,
    ) {
        parent::__construct($slug, $path, $date);
    }

    /**
     * @return array<string, mixed>
     */
    public function getExif(): array
    {
        return $this->exif;
    }

    public function offsetExists(mixed $offset): bool
    {
        return match ($offset) {
            'exif' => true,
            default => parent::offsetExists($offset),
        };
    }

    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            'exif' => $this->exif,
            default => parent::offsetGet($offset),
        };
    }
}
