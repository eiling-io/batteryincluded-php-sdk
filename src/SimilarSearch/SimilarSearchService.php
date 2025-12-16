<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\SimilarSearch;

use BatteryIncludedSdk\Client\ApiClient;

class SimilarSearchService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function search(string $query, string $locale = 'DE'): SimilarSearchResponse
    {
        $query = http_build_query(
            [
                'q' => $query,
                'v[locale]' => $locale,
            ]
        );

        $response = $this->apiClient->getJson(
            '/documents/similar-search?' . $query,
            []
        );

        return new SimilarSearchResponse($response->getRawResponse());
    }
}
