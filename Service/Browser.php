<?php

namespace Tom32i\ShowcaseBundle\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * File Browser
 */
class Browser
{
    /**
     * Image path
     */
    protected string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * List directories
     */
    public function list($sortBy = null, $sortContentBy = null, $filterBy = null, $filterContentBy = null): array
    {
        $groups = [];
        $finder = new Finder();
        $finder->in($this->path)->directories()->sortByModifiedTime();

        foreach ($finder as $directory) {
            $groups[] = $this->readDirectory($directory, $sortContentBy, $filterContentBy);
        }

        if ($sorter = $this->getSortFunction($sortBy)) {
            usort($groups, $sorter);
        }

        if ($filter = $this->getFilterFunction($filterBy)) {
            $groups = array_values(array_filter($groups, $filter));
        }

        return $groups;
    }

    public function read(string $path, $sortBy = null, $filterBy = null): ?array
    {
        $finder = new Finder();
        $directories = iterator_to_array($finder->in($this->path)->name($path)->directories(), false);

        if (empty($directories)) {
            return null;
        }

        return $this->readDirectory($directories[0], $sortBy, $filterBy);
    }

    /**
     * Read a directory
     *
     * @param string $name
     *
     * @return array
     */
    private function readDirectory(SplFileInfo $directory, $sortBy = null, $filterBy = null): array
    {
        $finder = new Finder();
        $finder->in($directory->getPathname())->files();

        $images = [];
        $videos = [];
        $config = [];
        $archive = null;

        foreach ($finder as $file) {
            $extention = $file->getExtension();

            if (preg_match('#jpg|jpeg|png|gif|webp#i', $extention)) {
                $images[] = $this->readImage($file, $directory);
            }

            if (preg_match('#mov#i', $extention)) {
                $videos[] = $this->readVideo($file);
            }

            if (preg_match('#zip#i', $extention)) {
                $archive = $this->readArchive($file);
            }

            if (preg_match('#json#i', $extention)) {
                $config = json_decode($file->getContents(), true);
            }
        }

        if ($sorter = $this->getSortFunction($sortBy)) {
            usort($images, $sorter);
        }

        if ($filter = $this->getFilterFunction($filterBy)) {
            $images = array_values(array_filter($images, $filter));
        }

        return array_merge($config, [
            'slug' => $directory->getBasename(),
            'images' => $images,
            'videos' => $videos,
            'archive' => $archive,
        ]);
    }

    public function readImage(SplFileInfo $file, SplFileInfo $directory)
    {
        $exif = @exif_read_data($file->getPathname());

        return [
            'slug' => $file->getBasename(),
            'path' => sprintf('%s/%s', $directory->getBasename(), $file->getBasename()),
            'exif' => $exif,
            'date' => $exif && isset($exif['DateTime']) ? $exif['DateTime'] : $file->getMTime(),
        ];
    }

    public function readVideo(SplFileInfo $file)
    {
        return [
            'slug' => $file->getBasename(),
            'path' => $file->getPathname(),
        ];
    }

    public function readArchive(SplFileInfo $file)
    {
        return [
            'slug' => $file->getBasename(),
            'path' => $file->getPathname(),
        ];
    }

    private function getSortFunction($sortBy): ?callable
    {
        if (!$sortBy) {
            return null;
        }

        if (is_callable($sortBy)) {
            return $sortBy;
        }

        if (is_array($sortBy)) {
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

        if (is_string($sortBy)) {
            return $this->getSortFunction([$sortBy => true]);
        }

        throw new \Exception('Could determine a sorter function');
    }

    private function getFilterFunction($filter): ?callable
    {
        if (!$filter) {
            return null;
        }

        if (is_callable($filter)) {
            return $filter;
        }

        if (is_array($filter)) {
            $key = array_keys($filter)[0];
            $value = array_values($filter)[0];

            return function ($data) use ($key, $value) {
                return $value == $this->propertyAccessor->getValue($data, $key);
            };
        }

        if (is_string($filter)) {
            return $this->getFilterFunction([$filter => true]);
        }

        throw new \Exception('Could determine a filter function');
    }
}
