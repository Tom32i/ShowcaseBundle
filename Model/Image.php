<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Model;

class Image extends File
{
    /**
     * @param array<string, mixed>  $exif
     * @param array<string, string> $props
     */
    public function __construct(
        Group $group,
        string $slug,
        \DateTimeImmutable $date,
        protected readonly array $exif = [],
        protected array $props = [],
    ) {
        parent::__construct($group, $slug, $date);
    }

    /**
     * @return array<string, mixed>
     */
    public function getExif(): array
    {
        return $this->exif;
    }

    /**
     * @return array<string, string>
     */
    public function getProps(): array
    {
        return $this->props;
    }

    public function setProps(string $key, string $value): void
    {
        $this->props[$key] = $value;
    }

    public function offsetExists(mixed $offset): bool
    {
        return match ($offset) {
            'exif' => true,
            'props' => true,
            default => parent::offsetExists($offset) || isset($this->exif[$offset]) || isset($this->props[$offset]),
        };
    }

    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            'exif' => $this->exif,
            'props' => $this->props,
            default => parent::offsetGet($offset) ?? $this->exif[$offset] ?? $this->props[$offset] ?? null,
        };
    }
}
