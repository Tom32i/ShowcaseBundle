<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Tom32i\ShowcaseBundle\Service\PresetManager;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private PresetManager $presetManager
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('image', [$this, 'getImage']),
            new TwigFunction('download', [$this, 'getUrl']),
            new TwigFunction('dimensions', [$this, 'getDimensions']),
        ];
    }

    public function getImage(string $path, string $name): string
    {
        $preset = $this->presetManager->getPreset($name);
        $data = pathinfo($path);

        if (!\array_key_exists('dirname', $data)) {
            throw new \Exception("Could not resolve directory name on \"$path\".");
        }

        if (!\array_key_exists('extension', $data)) {
            throw new \Exception("Could not resolve file extension on \"$path\".");
        }

        return $this->urlGenerator->generate('image', [
            'preset' => $preset->getName(),
            'path' => sprintf('%s/%s.%s', $data['dirname'], $data['filename'], $preset->getFormat() ?? $data['extension']),
        ]);
    }

    public function getUrl(string $path): string
    {
        return $this->urlGenerator->generate('file', [
            'path' => $path,
        ]);
    }

    /**
     * @return array<string,?int>
     */
    public function getDimensions(string $name): array
    {
        $preset = $this->presetManager->getPreset($name);

        return [
            'width' => $preset->getWidth() ?? null,
            'height' => $preset->getHeight() ?? null,
        ];
    }
}
