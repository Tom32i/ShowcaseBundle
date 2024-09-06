<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\ValueResolver;

use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Tom32i\ShowcaseBundle\Model\Group;
use Tom32i\ShowcaseBundle\Model\Image;
use Tom32i\ShowcaseBundle\Service\Browser;

class ImageValueResolver implements ValueResolverInterface
{
    /**
     * @param Browser<Group, Image> $browser
     */
    public function __construct(
        private Browser $browser,
    ) {
    }

    /**
     * @return array<Image>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argumentType = $argument->getType();

        if ($argumentType === null || !is_a($argumentType, Image::class, true)) {
            return [];
        }

        $value = $request->attributes->get($argument->getName());
        $slug = pathinfo($value, PATHINFO_DIRNAME);
        $name = pathinfo($value, PATHINFO_BASENAME);

        $group = $this->browser->read(
            $slug,
            ['[slug]' => true],
            loadProps: fn (Group $group, SplFileInfo $file) => $file->getBasename() === $name,
        );

        if ($group === null) {
            return [];
        }

        $image = $group->getImageBySlug($name);

        if ($image === null) {
            return [];
        }

        return [$image];
    }
}
