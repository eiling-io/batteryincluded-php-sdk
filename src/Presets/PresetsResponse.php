<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Presets;

use BatteryIncludedSdk\Service\Response;

class PresetsResponse extends Response
{
    private array $presets;

    public function __construct(string $responseRaw)
    {
        parent::__construct($responseRaw);
        $this->presets = [];
        foreach ($this->getBody() as $preset) {
            $this->presets[] = new PresetsDto(
                $preset['id'],
                $preset['name']
            );
        }
    }

    public function getPresets(): array
    {
        return $this->presets;
    }
}
