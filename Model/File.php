<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Model;

/** @implements \ArrayAccess<string, mixed> */
abstract class File implements \ArrayAccess
{
    public function __construct(
        protected string $slug,
        protected string $path,
        protected \DateTimeImmutable $date,
    ) {
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    public function offsetExists(mixed $offset): bool
    {
        return match ($offset) {
            'slug' => true,
            'path' => true,
            'date' => true,
            default => false,
        };
    }

    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            'slug' => $this->slug,
            'path' => $this->path,
            'date' => $this->date,
            default => null,
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
