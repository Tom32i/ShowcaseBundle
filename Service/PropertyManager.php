<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use Tom32i\ShowcaseBundle\Behavior\Properties;

class PropertyManager implements Properties
{
    /**
     * @return array<string,string>
     */
    public function all(string $path): array
    {
        $imagick = new \Imagick($path);
        $props = $imagick->getImageProperties();
        $imagick->clear();
        unset($imagick);

        return $props;
    }

    public function get(string $path, string $key): ?string
    {
        $imagick = new \Imagick($path);
        $value = $imagick->getImageProperty($key);
        $imagick->clear();
        unset($imagick);

        if ($value == false) {
            return null;
        }

        return $value;
    }

    public function set(string $path, string $key, string $value): bool
    {
        $imagick = new \Imagick($path);
        $result = false;

        if ($imagick->setImageProperty($key, $value)) {
            $result = $imagick->writeImage();
        }

        $imagick->clear();
        unset($imagick);

        return $result;
    }

    /**
     * @param array<string,string> $properties
     */
    public function setAll(string $path, array $properties): bool
    {
        $imagick = new \Imagick($path);

        foreach ($properties as $key => $value) {
            if (!$imagick->setImageProperty($key, $value)) {
                $imagick->clear();
                unset($imagick);
                throw new \Exception("Could not set propety \"$key\" to \"\" on file \"$path\".");
            }
        }

        $result = $imagick->writeImage();

        $imagick->clear();
        unset($imagick);

        return $result;
    }

    public function delete(string $path, string $key): bool
    {
        $imagick = new \Imagick($path);
        $imagick->deleteImageProperty($key);
        $imagick->clear();
        unset($imagick);

        return true;
    }
}
