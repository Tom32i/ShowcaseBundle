<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Command\Behaviour;

use Tom32i\ShowcaseBundle\Model\Group;

trait CommandHelper
{
    private function filterBySlug(?string $slug): ?callable
    {
        if ($slug === null) {
            return null;
        }

        return static fn (Group $group): bool => $group->getSlug() === $slug;
    }

    private function parseString(mixed $source): ?string
    {
        if ($source === null) {
            return null;
        }

        $value = trim(\strval($source));

        if (\strlen($value) === 0) {
            return null;
        }

        return $value;
    }
}
