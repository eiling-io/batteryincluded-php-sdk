<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Presets;

use BatteryIncludedSdk\Client\ApiClient;

class PresetsService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function getPresets(): PresetsResponse
    {
        $response = $this->apiClient->getJson(
            '/documents/presets',
            []
        );

        return new PresetsResponse($response->getRawResponse(), $response->getStatusCode());
    }
}
