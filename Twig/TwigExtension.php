<?php

namespace Tom32i\ShowcaseBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(UrlGeneratorInterface $urlGenerator, array $presets = [])
    {
        $this->urlGenerator = $urlGenerator;
        $this->presets = $presets;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('image', [$this, 'getImage']),
            new TwigFunction('dimensions', [$this, 'getDimensions']),
            //new TwigFunction('video', [$this, 'getVideo']),
            //new TwigFunction('archive', [$this, 'getArchive']),
        ];
    }

    public function getImage(string $path, string $preset = null): string
    {
        return $this->urlGenerator->generate('image', [
            'path' => $path,
            'preset' => $preset,
        ]);
    }

    public function getDimensions(string $preset): array
    {
        if (!isset($this->presets[$preset])) {
            throw new \Exception("Preset unknown preset \"$preset\".");
        }

        return [
            'width' => $this->presets[$preset]['w'] ?: null,
            'height' => $this->presets[$preset]['h'] ?: null,
        ];
    }
}
