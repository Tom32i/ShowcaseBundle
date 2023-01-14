<?php

declare(strict_types=1);

namespace Tom32i\ShowcaseBundle\Service;

use Tom32i\ShowcaseBundle\Model\Preset;

class PresetManager
{
    /**
     * @var Preset[]
     */
    private array $presets = [];

    /**
     * @param array<string,array<string, mixed>> $presets
     */
    public function __construct(array $presets = [])
    {
        foreach ($presets as $name => $config) {
            $this->addPreset(Preset::createFromConfig($name, $config));
        }
    }

    public function addPreset(Preset $preset): void
    {
        if (isset($this->presets[$preset->getName()])) {
            throw new \Exception("Preset with name \"{$preset->getName()}\" is already set.");
        }

        $this->presets[$preset->getName()] = $preset;
    }

    public function getPreset(string $name): Preset
    {
        if (!isset($this->presets[$name])) {
            throw new \Exception("Preset with name \"$name\" could not be found.");
        }

        return $this->presets[$name];
    }

    /**
     * @return Preset[]
     */
    public function getAll(): array
    {
        return $this->presets;
    }

    /**
     * @return array<string,array<string, mixed>>
     */
    public function getConfig(): array
    {
        return array_map(
            static fn (Preset $preset): array => $preset->getConfig(),
            $this->presets
        );
    }
}
