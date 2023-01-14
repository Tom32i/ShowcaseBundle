<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Command\Behaviour;

trait CommandHelper
{
    private function parseString(?string $source): ?string
    {
        $value = trim($source);

        if (\strlen($value) === 0) {
            return null;
        }

        return $value;
    }
}
