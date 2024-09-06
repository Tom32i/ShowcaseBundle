<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Command\Behaviour;

trait CommandHelper
{
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
