<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\ValueResolver\Attribute;

use Symfony\Component\Finder\SplFileInfo;
use Tom32i\ShowcaseBundle\Model\Group;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class LoadOption
{
    public const DISABLED = 'disabled';
    public const ONLY_FIRST = 'onlyFirst';

    public function __construct(
        public string $mode,
    ) {
    }

    public function getFilter(): ?callable
    {
        return match ($this->mode) {
            self::DISABLED => self::disabled(...),
            self::ONLY_FIRST => self::onlyFirst(...),
            default => null,
        };
    }

    public static function onlyFirst(Group $group, SplFileInfo $file): bool
    {
        return \count($group->getImages()) === 0;
    }

    public static function disabled(Group $group, SplFileInfo $file): bool
    {
        return false;
    }
}
