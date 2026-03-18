<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

use BatteryIncludedSdk\Client\ApiClient;

class BrowseService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function browse(BrowseSearchStruct $searchStruct): BrowseResponse
    {
        $queryArray = [
            'q' => $searchStruct->getQuery(),
            'f' => $searchStruct->getFilters(),
            'v' => $searchStruct->getVariables(),
            'page' => $searchStruct->getPage(),
            'per_page' => $searchStruct->getPerPage(),
            'sort' => $searchStruct->getSort(),
            'preset' => $searchStruct->getPresetId(),
        ];

        $query = http_build_query($queryArray);

        $response = $this->apiClient->getJson(
            '/documents/browse?' . $query,
            []
        );

        return new BrowseResponse($response->getRawResponse(), $searchStruct);
    }
}
