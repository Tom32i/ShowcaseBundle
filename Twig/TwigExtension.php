<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Twig;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension
{
    private UrlGeneratorInterface $urlGenerator;
    private array $presets;

    public function __construct(UrlGeneratorInterface $urlGenerator, array $presets = [])
    {
        $this->urlGenerator = $urlGenerator;
        $this->presets = $presets;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('image', [$this, 'getImage']),
            new TwigFunction('download', [$this, 'getUrl']),
            new TwigFunction('dimensions', [$this, 'getDimensions']),
        ];
    }

    public function getImage(string $path, string $name = null): string
    {
        $preset = $this->getPreset($name);
        $data = pathinfo($path);

        return $this->urlGenerator->generate('image', [
            'preset' => $name,
            'path' => sprintf('%s/%s.%s', $data['dirname'], $data['filename'], $preset['fm'] ?? $data['extension']),
        ]);
    }

    public function getUrl(string $path): string
    {
        return $this->urlGenerator->generate('file', [
            'path' => $path,
        ]);
    }

    public function getDimensions(string $name): array
    {
        $preset = $this->getPreset($name);

        return [
            'width' => $preset['w'] ?: null,
            'height' => $preset['h'] ?: null,
        ];
    }

    private function getPreset(string $preset): array
    {
        if (!isset($this->presets[$preset])) {
            throw new \Exception("Preset unknown preset \"$preset\".");
        }

        return $this->presets[$preset];
    }
}
