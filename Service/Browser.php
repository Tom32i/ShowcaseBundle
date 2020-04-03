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
    public function list($sortBy = null, $sortContentBy = null): array
    {
        /*if (!file_exists($this->path) || !is_dir($this->path)) {
            return null;
        }*/

        $groups = [];
        $finder = new Finder();
        $finder->in($this->path)->directories()->sortByModifiedTime();

        foreach ($finder as $directory) {
            $groups[] = $this->readDirectory($directory, $sortContentBy);
        }

        if ($sorter = $this->getSortFunction($sortBy)) {
            usort($groups, $sorter);
        }

        return $groups;
    }

    public function read(string $path, $sortBy = null): ?array
    {
        $finder = new Finder();
        $directories = iterator_to_array($finder->in($this->path)->name($path)->directories(), false);

        if (empty($directories)) {
            return null;
        }

        return $this->readDirectory($directories[0], $sortBy);
    }

    /**
     * Read a directory
     *
     * @param string $name
     *
     * @return array
     */
    private function readDirectory(SplFileInfo $directory, $sortBy = null): array
    {
        $finder = new Finder();
        $finder->in($directory->getPathname())->files();

        $images = [];
        $videos = [];
        $config = [];
        $archive = null;
        //$sort = 'sortByDateAsc';

        foreach ($finder as $file) {
            $extention = $file->getExtension();

            if (preg_match('#jpg|jpeg|png|gif#i', $extention)) {
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

        /*try {
            usort($images, [$this, $sort]);
        } catch (\Exception $exception) {
            usort($images, [$this, 'sortByDateAsc']);
        }*/

        if ($sorter = $this->getSortFunction($sortBy)) {
            usort($images, $sorter);
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
            //'url' =>
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

        throw new \Exception('Unknown sorter');
    }
}
