<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * File Browser
 */
class Browser
{
    public function __construct(
        private PropertyAccessor $propertyAccessor,
        private string $path
    ) {
    }

    /**
     * List all directories
     *
     * @return array<int, array<string, mixed>>
     */
    public function list(
        mixed $sortBy = null,
        mixed $sortContentBy = null,
        mixed $filterBy = null,
        mixed $filterContentBy = null
    ): array {
        $groups = [];
        $finder = new Finder();
        $finder->in($this->path)->directories()->sortByModifiedTime();

        foreach ($finder as $directory) {
            $groups[] = $this->readDirectory($directory, $sortContentBy, $filterContentBy);
        }

        if (($sorter = $this->getSortFunction($sortBy)) !== null) {
            usort($groups, $sorter);
        }

        if (($filter = $this->getFilterFunction($filterBy)) !== null) {
            $groups = array_values(array_filter($groups, $filter));
        }

        return $groups;
    }

    /**
     * Read a single directory
     *
     * @return array<string, mixed>
     */
    public function read(
        string $path,
        mixed $sortBy = null,
        mixed $filterBy = null
    ): ?array {
        $finder = new Finder();
        $directories = iterator_to_array($finder->in($this->path)->name($path)->directories(), false);

        if (\count($directories) === 0) {
            return null;
        }

        return $this->readDirectory($directories[0], $sortBy, $filterBy);
    }

    /**
     * @return array<string, string>
     */
    private function readDirectory(
        SplFileInfo $directory,
        mixed $sortBy = null,
        mixed $filterBy = null
    ): array {
        $finder = new Finder();
        $finder->in($directory->getPathname())->files();

        $images = [];
        $videos = [];
        $config = [];
        $archive = null;

        foreach ($finder as $file) {
            $extention = $file->getExtension();

            if (preg_match('#jpg|jpeg|png|gif|webp#i', $extention) === 1) {
                $images[] = $this->readImage($file, $directory);
            }

            if (preg_match('#webm|mp4|m4a|m4p|m4b|m4r|m4v|ogg|oga|ogv|ogx|spx|opus#i', $extention) === 1) {
                $videos[] = $this->readVideo($file, $directory);
            }

            if (preg_match('#zip#i', $extention) === 1) {
                $archive = $this->readArchive($file, $directory);
            }

            if (preg_match('#json#i', $extention) === 1) {
                $config = json_decode($file->getContents(), true);

                if (!\is_array($config)) {
                    throw new \Exception('Config file ' . $file->getPathname() . ' content must be an array, "' . \gettype($config) . '" given.');
                }
            }
        }

        if (($sorter = $this->getSortFunction($sortBy)) !== null) {
            usort($images, $sorter);
        }

        if (($filter = $this->getFilterFunction($filterBy)) !== null) {
            $images = array_values(array_filter($images, $filter));
        }

        return array_merge($config, [
            'slug' => $directory->getBasename(),
            'images' => $images,
            'videos' => $videos,
            'archive' => $archive,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function readImage(SplFileInfo $file, SplFileInfo $directory)
    {
        try {
            $exif = exif_read_data($file->getPathname());
        } catch (\ErrorException) {
            $exif = [];
        }

        return [
            'slug' => $file->getBasename(),
            'path' => sprintf('%s/%s', $directory->getBasename(), $file->getBasename()),
            'exif' => $exif,
            'date' => $exif['DateTime'] ?? $file->getMTime(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function readVideo(SplFileInfo $file, SplFileInfo $directory)
    {
        return [
            'slug' => $file->getBasename(),
            'path' => sprintf('%s/%s', $directory->getBasename(), $file->getBasename()),
            'date' => $file->getMTime(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function readArchive(SplFileInfo $file, SplFileInfo $directory): array
    {
        return [
            'slug' => $file->getBasename(),
            'path' => sprintf('%s/%s', $directory->getBasename(), $file->getBasename()),
            'date' => $file->getMTime(),
        ];
    }

    private function getSortFunction(mixed $sortBy = null): ?callable
    {
        if ($sortBy === null) {
            return null;
        }

        if (\is_callable($sortBy)) {
            return $sortBy;
        }

        if (\is_array($sortBy)) {
            $key = array_keys($sortBy)[0];
            $asc = (bool) array_values($sortBy)[0];

            return function ($a, $b) use ($key, $asc) {
                $valueA = $this->propertyAccessor->getValue($a, $key);
                $valueB = $this->propertyAccessor->getValue($b, $key);

                if ($valueA == $valueB) {
                    return 0;
                }

                return ($valueA > $valueB) === $asc ? 1 : -1;
            };
        }

        if (\is_string($sortBy)) {
            return $this->getSortFunction([$sortBy => true]);
        }

        throw new \Exception('Could not determine a sorter function');
    }

    private function getFilterFunction(mixed $filter = null): ?callable
    {
        if ($filter === null) {
            return null;
        }

        if (\is_callable($filter)) {
            return $filter;
        }

        if (\is_array($filter)) {
            $key = array_keys($filter)[0];
            $value = array_values($filter)[0];

            return function ($data) use ($key, $value) {
                return $value == $this->propertyAccessor->getValue($data, $key);
            };
        }

        if (\is_string($filter)) {
            return $this->getFilterFunction([$filter => true]);
        }

        throw new \Exception('Could not determine a filter function');
    }
}
