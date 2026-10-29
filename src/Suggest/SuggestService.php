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

    public function suggestWithFilter(SuggestSearchStruct $searchStruct): SuggestResponse
    {
        // the gateway fails without locale as soon as the collection uses translated facets
        $locale = I18nResolver::normalizeLocale($searchStruct->getLocale());
        $variables = ['locale' => $locale];
        $availability = $searchStruct->getAvailability();
        if ($availability !== null) {
            $availability = AvailabilityResolver::normalizeScope($availability);
            $variables[AvailabilityResolver::VARIABLE] = $availability;
        }

        $query = http_build_query(
            [
                'q' => $searchStruct->getQuery(),
                'f' => $searchStruct->getFilters(),
                'v' => $variables,
            ]
        );

        $response = $this->apiClient->getJson('/documents/suggest?' . $query);

        return new SuggestResponse($response->getRawResponse(), $response->getStatusCode(), $locale, $availability);
    }
}
