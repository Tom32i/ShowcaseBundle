<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Model;

/** @implements \ArrayAccess<string, mixed> */
class Group implements \ArrayAccess
{
    /**
     * @param Image[]              $images
     * @param Video[]              $videos
     * @param array<string, mixed> $config
     */
    public function __construct(
        private string $slug,
        private array $images,
        private array $videos,
        private ?Archive $archive,
        private array $config,
    ) {
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return Image[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @return Video[]
     */
    public function getVideos(): array
    {
        return $this->videos;
    }

    public function getArchive(): ?Archive
    {
        return $this->archive;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    public function offsetExists(mixed $offset): bool
    {
        return match ($offset) {
            'slug' => true,
            'images' => true,
            'videos' => true,
            'archive' => true,
            default => isset($this->config[$offset]),
        };
    }

    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            'slug' => $this->slug,
            'images' => $this->images,
            'videos' => $this->videos,
            'archive' => $this->archive,
            default => $this->config[$offset] ?? null
        };
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new \Exception('This object is readonly.');
    }

    public function offsetUnset(mixed $offset): void
    {
        throw new \Exception('This object is readonly.');
    }
}
