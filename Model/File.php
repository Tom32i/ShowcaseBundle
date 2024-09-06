<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Model;

/** @implements \ArrayAccess<string, mixed> */
abstract class File implements \ArrayAccess
{
    public function __construct(
        protected Group $group,
        protected string $slug,
        protected \DateTimeImmutable $date,
    ) {
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getPath(): string
    {
        return \sprintf('%s/%s', $this->group->getSlug(), $this->slug);
    }

    public function getExtension(): string
    {
        return pathinfo($this->slug, PATHINFO_EXTENSION);
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
            'path' => $this->getPath(),
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
