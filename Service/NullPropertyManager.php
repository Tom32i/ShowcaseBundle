<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use Tom32i\ShowcaseBundle\Behavior\Properties;

class NullPropertyManager implements Properties
{
    public function all(string $path): array
    {
        return [];
    }

    public function get(string $path, string $key): ?string
    {
        return null;
    }

    public function set(string $path, string $key, string $value): bool
    {
        return false;
    }

    public function setAll(string $path, array $properties): bool
    {
        return false;
    }

    public function delete(string $path, string $key): bool
    {
        return false;
    }
}
