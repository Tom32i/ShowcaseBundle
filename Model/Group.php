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
        protected string $slug,
        protected array $images = [],
        protected array $videos = [],
        protected ?Archive $archive = null,
        protected array $config = [],
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

    public function getImage(int $index): ?Image
    {
        return $this->images[$index] ?? null;
    }

    public function getImageBySlug(string $slug): ?Image
    {
        foreach ($this->images as $image) {
            if ($image->getSlug() === $slug) {
                return $image;
            }
        }

        return null;
    }

    public function addImage(Image $image): void
    {
        $this->images[] = $image;
    }

    /**
     * @return Video[]
     */
    public function getVideos(): array
    {
        return $this->videos;
    }

    public function addVideo(Video $video): void
    {
        $this->videos[] = $video;
    }

    public function getArchive(): ?Archive
    {
        return $this->archive;
    }

    public function setArchive(Archive $archive): void
    {
        $this->archive = $archive;
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function sortImages(callable $sorter): void
    {
        usort($this->images, $sorter);
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
            default => $this->config[$offset] ?? null,
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
