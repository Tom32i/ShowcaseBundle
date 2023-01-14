<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Model;

class Preset
{
    public function __construct(
        private string $name,
        private ?int $width,
        private ?int $height,
        private ?string $format,
        private ?string $fit,
        private ?float $dpr = 1.0,
        private ?int $quality = 100,
    ) {
    }

    /**
     * @param array<string, mixed> $config
     */
    public static function createFromConfig(string $name, array $config): self
    {
        return new self(
            $name,
            isset($config['w']) ? \intval($config['w']) : null,
            isset($config['h']) ? \intval($config['h']) : null,
            isset($config['fm']) ? \strval($config['fm']) : null,
            isset($config['fit']) ? \strval($config['fit']) : null,
            isset($config['dpr']) ? \floatval($config['dpr']) : null,
            isset($config['q']) ? \intval($config['q']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfig(): array
    {
        return [
            'w' => $this->width,
            'h' => $this->height,
            'dpr' => $this->dpr,
            'fit' => $this->fit,
            'fm' => $this->format,
            'q' => $this->quality,
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }
}
