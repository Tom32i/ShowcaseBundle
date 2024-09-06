<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Tom32i\ShowcaseBundle\Behavior\Properties;
use Tom32i\ShowcaseBundle\Model\Archive;
use Tom32i\ShowcaseBundle\Model\Group;
use Tom32i\ShowcaseBundle\Model\Image;
use Tom32i\ShowcaseBundle\Model\Video;

/**
 * File Browser
 *
 * @template G of Group
 * @template I of Image
 */
class Browser
{
    /**
     * @param class-string<G> $groupClass
     * @param class-string<I> $imageClass
     */
    public function __construct(
        private PropertyAccessor $propertyAccessor,
        private Properties $properties,
        private string $path,
        private string $groupClass = Group::class,
        private string $imageClass = Image::class,
    ) {
    }

    /**
     * List all directories
     *
     * @return G[]
     */
    public function list(
        mixed $sortBy = null,
        mixed $sortContentBy = null,
        mixed $filterBy = null,
        mixed $filterContentBy = null,
        ?callable $loadProps = null,
    ): array {
        $groups = [];
        $finder = new Finder();
        $finder->in($this->path)->directories();

        foreach ($finder as $directory) {
            $groups[] = $this->loadDirectory($directory, $sortContentBy, $filterContentBy, $loadProps);
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
     * @return ?G
     */
    public function read(
        string $path,
        mixed $sortBy = null,
        mixed $filterBy = null,
        ?callable $loadProps = null,
    ): ?Group {
        $finder = new Finder();
        $finder->in($this->path)->name($path)->directories();
        $directories = iterator_to_array($finder, false);

        if (\count($directories) === 0) {
            return null;
        }

        return $this->loadDirectory(reset($directories), $sortBy, $filterBy, $loadProps);
    }

    /**
     * Load a directory
     *
     * @return G
     */
    private function loadDirectory(
        SplFileInfo $directory,
        mixed $sortBy = null,
        mixed $filterBy = null,
        ?callable $loadProps = null,
    ): Group {
        $finder = new Finder();
        $finder->in($directory->getPathname())->files()->sortByName();
        /** @var G */
        $group = new ($this->groupClass)($directory->getBasename());

        foreach ($finder as $file) {
            $extention = strtolower($file->getExtension());

            if (preg_match('#jpe?g|png|gif|webp#i', $extention) === 1) {
                $loadMeta = $loadProps === null || $loadProps($group, $file);
                $group->addImage($this->readImage($group, $file, $directory, $extention, $loadMeta));
            }

            if (preg_match('#webm|mp4|m4a|m4p|m4b|m4r|m4v|ogg|oga|ogv|ogx|spx|opus#i', $extention) === 1) {
                $group->addVideo($this->readVideo($group, $file, $directory));
            }

            if (preg_match('#zip#i', $extention) === 1) {
                $group->setArchive($this->readArchive($group, $file, $directory));
            }

            if (preg_match('#json#i', $extention) === 1) {
                $group->setConfig($this->loadConfig($file));
            }
        }

        if (($sorter = $this->getSortFunction($sortBy)) !== null) {
            $group->sortImages($sorter);
        }

        return $group;
    }

    /**
     * @return array<string,string>
     */
    private function loadConfig(SplFileInfo $file): array
    {
        $config = json_decode($file->getContents(), true);

        if (!\is_array($config)) {
            throw new \Exception('Config file ' . $file->getPathname() . ' content must be an array, "' . \gettype($config) . '" given.');
        }

        return $config;
    }

    /**
     * [readImage description]
     *
     * @param G $group
     *
     * @return I
     */
    private function readImage(Group $group, SplFileInfo $file, SplFileInfo $directory, string $extention, bool $loadMeta = true): Image
    {
        $exif = [];
        $props = [];

        if ($loadMeta && $extention === 'jpg' || $extention === 'jpeg') {
            $exif = $this->getExif($file);
        }

        if ($loadMeta && $extention === 'png') {
            $props = $this->properties->all($file->getPathname());
        }

        /** @var I */
        $image = new ($this->imageClass)(
            $group,
            $file->getBasename(),
            $this->getDate($file, $exif, $props),
            $exif,
            $props,
        );

        return $image;
    }

    /**
     * @return array<string,mixed>
     */
    private function getExif(SplFileInfo $file): array
    {
        try {
            $exif = @exif_read_data($file->getPathname());
        } catch (\ErrorException) {
            $exif = false;
        }

        if ($exif === false) {
            return [];
        }

        return $exif;
    }

    /**
     * @param array<string,mixed>  $exif
     * @param array<string,string> $props
     */
    private function getDate(SplFileInfo $file, array $exif, array $props): \DateTimeImmutable
    {
        if (isset($props['date:create'])) {
            return new \DateTimeImmutable($props['date:create']);
        }

        if (isset($exif['DateTime'])) {
            return new \DateTimeImmutable($exif['DateTime']);
        }

        return (new \DateTimeImmutable())->setTimestamp($file->getMTime());
    }

    private function readVideo(Group $group, SplFileInfo $file, SplFileInfo $directory): Video
    {
        return new Video(
            $group,
            $file->getBasename(),
            (new \DateTimeImmutable())->setTimestamp($file->getMTime()),
        );
    }

    private function readArchive(Group $group, SplFileInfo $file, SplFileInfo $directory): Archive
    {
        return new Archive(
            $group,
            $file->getBasename(),
            (new \DateTimeImmutable())->setTimestamp($file->getMTime())
        );
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
