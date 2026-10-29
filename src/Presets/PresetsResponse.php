<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Presets;

use BatteryIncludedSdk\Service\Response;

class PresetsResponse extends Response
{
    private array $presets;

    public function __construct(string $responseRaw, int $statusCode)
    {
        parent::__construct($responseRaw, $statusCode);
        $this->presets = [];
        foreach ($this->getBody() as $preset) {
            if (!is_array($preset) || !isset($preset['id'], $preset['name'])) {
                continue;
            }

            $this->presets[] = new PresetsDto(
                (string) $preset['id'],
                (string) $preset['name']
            );
        }
    }

    /**
     * @return PresetsDto[]
     */
    public function getPresets(): array
    {
        return $this->presets;
    }
}
