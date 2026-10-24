<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Suggest;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\I18n\I18nResolver;

class SuggestService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    /**
     * @deprecated will be removed in 1.0.0, use suggestWithFilter instead
     */
    public function suggest(string $query, string $locale = 'DE'): SuggestResponse
    {
        $query = http_build_query(
            [
                'q' => $query,
                'v' => ['locale' => I18nResolver::normalizeLocale($locale)],
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
        // the gateway fails without locale as soon as the collection uses translated facets
        $variables = ['locale' => I18nResolver::normalizeLocale($searchStruct->getLocale())];
        if ($searchStruct->getAvailability() !== null) {
            $variables[AvailabilityResolver::VARIABLE] = AvailabilityResolver::normalizeScope($searchStruct->getAvailability());
        }

        $query = http_build_query(
            [
                'q' => $searchStruct->getQuery(),
                'f' => $searchStruct->getFilters(),
                'v' => $variables,
            ]
        );

        $response = $this->apiClient->getJson(
            '/documents/suggest?' . $query,
            []
        );

        return new SuggestResponse($response->getRawResponse(), $response->getStatusCode());
    }
}
