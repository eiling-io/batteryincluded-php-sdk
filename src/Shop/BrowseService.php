<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\I18n\I18nResolver;

class BrowseService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function browse(BrowseSearchStruct $searchStruct): BrowseResponse
    {
        $variables = array_merge(['locale' => $searchStruct->getLocale()], $searchStruct->getVariables());
        // the gateway selects `_PRODUCT_i18n.<locale>` by key, the locales are exported normalized
        $variables['locale'] = I18nResolver::normalizeLocale((string) $variables['locale']);
        $availability = $variables[AvailabilityResolver::VARIABLE] ?? $searchStruct->getAvailability();
        if ($availability !== null) {
            $variables[AvailabilityResolver::VARIABLE] = AvailabilityResolver::normalizeScope((string) $availability);
        }

        $queryArray = [
            'q' => $searchStruct->getQuery(),
            'f' => $searchStruct->getFilters(),
            'v' => $variables,
            'page' => $searchStruct->getPage(),
            'per_page' => $searchStruct->getPerPage(),
            'sort' => $searchStruct->getSort(),
            'preset' => $searchStruct->getPresetId(),
        ];

        $query = http_build_query($queryArray);

        $response = $this->apiClient->getJson('/documents/browse?' . $query);

        return new BrowseResponse($response->getRawResponse(), $searchStruct, $response->getStatusCode());
    }
}
