<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use Tom32i\ShowcaseBundle\Model\File;
use Tom32i\ShowcaseBundle\Model\Group;

class Normalizer
{
    public const PATTERN = '%prefix%-%index%.%extension%';

    public function __construct(
        private Processor $processor,
        private string $path,
    ) {
    }

    public function normalize(Group $group, ?string $pattern = null, bool $shuffle = false, ?callable $onFile = null): void
    {
        $tmpDir = sys_get_temp_dir();
        $images = $group->getImages();

        if ($shuffle) {
            shuffle($images);
        }

        $length = $this->getLength(\count($images));

        foreach ($images as $index => $file) {
            $this->moveToDirectory($file, $tmpDir);
        }

        foreach (array_values($images) as $index => $file) {
            $newSlug = $this->generateName(
                $pattern ?? self::PATTERN,
                $group->getSlug(),
                $index,
                $file->getExtension(),
                $length
            );

            $this->rename($file, $tmpDir, $newSlug);

            if ($onFile !== null) {
                $onFile($index, $file);
            }
        }
    }

    private function moveToDirectory(File $file, string $directory): bool
    {
        return rename(
            \sprintf('%s/%s', $this->path, $file->getPath()),
            \sprintf('%s/%s', $directory, $file->getSlug())
        );
    }

    private function rename(File $file, string $directory, string $newSlug): void
    {
        $slug = $file->getSlug();

        $file->setSlug($newSlug);

        rename(
            \sprintf('%s/%s', $directory, $slug),
            \sprintf('%s/%s', $this->path, $file->getPath())
        );

        if ($slug !== $newSlug) {
            $this->processor->clear($file->getPath());
        }
    }

    private function generateName(string $pattern, string $prefix, int $index, string $extension, int $length = 2): string
    {
        return str_replace(
            [
                '%prefix%',
                '%index%',
                '%extension%',
            ],
            [
                $prefix,
                str_pad((string) ($index + 1), $length, '0', STR_PAD_LEFT),
                strtolower($extension),
            ],
            $pattern
        );
    }

    private function getLength(int $count): int
    {
        $i = 1;

        while (pow(10, $i) <= $count) {
            ++$i;
        }

        return $i;
    }
}
