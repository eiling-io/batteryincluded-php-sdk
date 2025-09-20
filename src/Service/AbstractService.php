<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

abstract class AbstractService
{
    public function generateNDJSON(array $data): string
    {
        // @TODO make configurable
        $jsonFlags = 0;
        $jsonFlags &= ~JSON_PRETTY_PRINT;

        $jsonRaw = array_map(static function ($value) use ($jsonFlags) {
            return json_encode($value, $jsonFlags);
        }, $data);

        return "\n" . implode("\n", $jsonRaw) . "\n";
    }
}
