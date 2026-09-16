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
        $variables = $searchStruct->getVariables();
        $locale = $searchStruct->getLocale() ?? $variables['locale'] ?? $this->apiClient->getDefaultLocale();

        $queryArray = [
            'q' => $searchStruct->getQuery(),
            'f' => $searchStruct->getFilters(),
            'v' => ['locale' => $locale] + $variables,
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
