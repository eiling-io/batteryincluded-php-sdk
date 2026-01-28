<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Recommendations;

use BatteryIncludedSdk\Client\ApiClient;

class RecommendationsService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function recommendByIdentifier(string $identifier, string $type = 'PRODUCT'): RecommendationsResponse
    {
        $query = http_build_query(
            [
                'id' => $type . '-' . $identifier,
            ]
        );

        $response = $this->apiClient->getJson(
            '/documents/recommendations?' . $query,
            []
        );

        return new RecommendationsResponse($response->getRawResponse(), $response->getStatusCode());
    }
}
