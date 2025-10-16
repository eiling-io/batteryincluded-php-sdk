<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Suggest;

use BatteryIncludedSdk\Client\ApiClient;

class SuggestService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function suggest(string $query): SuggestResponse
    {
        $query = http_build_query(
            [
                'q' => $query,
            ]
        );

        $response = $this->apiClient->getJson(
            '/documents/suggest?' . $query,
            []
        );

        return new SuggestResponse($response->getRawResponse());
    }
}
