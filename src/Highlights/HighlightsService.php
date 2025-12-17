<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Highlights;

use BatteryIncludedSdk\Client\ApiClient;

class HighlightsService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function getHighlights(): HighlightsResponse
    {
        $response = $this->apiClient->getJson(
            '/documents/highlights',
            []
        );

        return new HighlightsResponse($response->getRawResponse());
    }
}
