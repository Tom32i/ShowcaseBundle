<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Tom32i\ShowcaseBundle\Behavior\Properties;

class CachedPropertyManager implements Properties
{
    public const string PREFIX = 'imagick';

    public function __construct(
        private Properties $inner,
        private FilesystemAdapter $cache,
    ) {
    }

    public function all(string $path): array
    {
        $item = $this->cache->getItem($this->getCacheKey($path));

        if (!$item->isHit()) {
            $item->set($this->inner->all($path));
            $this->cache->save($item);
        }

        return $item->get();
    }

    public function get(string $path, string $key): ?string
    {
        return $this->all($path)[$key] ?? null;
    }

    public function set(string $path, string $key, string $value): bool
    {
        return $this->inner->set($path, $key, $value);
    }

    public function setAll(string $path, array $properties): bool
    {
        return $this->inner->setAll($path, $properties);
    }

    public function delete(string $path, string $key): bool
    {
        return $this->inner->delete($path, $key);
    }

    private function getCacheKey(string $path): string
    {
        return implode('-', [self::PREFIX, hash_file('md5', $path)]);
    }
}
