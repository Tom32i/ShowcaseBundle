<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Behavior;

interface Properties
{
    /**
     * @return array<string,string>
     */
    public function all(string $path): array;

    public function get(string $path, string $key): ?string;

    public function set(string $path, string $key, string $value): bool;

    /**
     * @param array<string,string> $properties
     */
    public function setAll(string $path, array $properties): bool;

    public function delete(string $path, string $key): bool;
}
