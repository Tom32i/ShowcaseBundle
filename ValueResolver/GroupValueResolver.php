<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\ValueResolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Tom32i\ShowcaseBundle\Model\Group;
use Tom32i\ShowcaseBundle\Model\Image;
use Tom32i\ShowcaseBundle\Service\Browser;
use Tom32i\ShowcaseBundle\ValueResolver\Attribute\LoadOption;

class GroupValueResolver implements ValueResolverInterface
{
    /**
     * @param Browser<Group, Image> $browser
     */
    public function __construct(
        private Browser $browser,
    ) {
    }

    /**
     * @return array<Group>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if ($argumentType === null || !is_a($argumentType, Group::class, true)) {
            return [];
        }

        $options = $argument->getAttributesOfType(LoadOption::class);
        $slug = $request->attributes->get($argument->getName());

        $group = $this->browser->read(
            $slug,
            ['[slug]' => true],
            loadProps: array_shift($options)?->getFilter() ?? null
        );

        if ($group === null) {
            return [];
        }

        return [$group];
    }
}
