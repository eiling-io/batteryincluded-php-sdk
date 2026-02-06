<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Suggest;

use BatteryIncludedSdk\Client\ApiClient;

class SuggestService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    /**
     * @deprecated will be removed in 1.0.0, use suggestWithFilter instead
     */
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

        return new SuggestResponse($response->getRawResponse(), $response->getStatusCode());
    }

    public function suggestWithFilter(SuggestSearchStruct $searchStruct): SuggestResponse
    {
        $query = http_build_query(
            [
                'q' => $searchStruct->getQuery(),
                'f' => $searchStruct->getFilters(),
            ]
        );

        $response = $this->apiClient->getJson(
            '/documents/suggest?' . $query,
            []
        );

        return new SuggestResponse($response->getRawResponse(), $response->getStatusCode());
    }
}
