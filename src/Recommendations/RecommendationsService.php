<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Recommendations;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\I18n\I18nResolver;

class RecommendationsService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    /**
     * @param string|null $locale locale of the translated fields, e.g. `de_DE`, sent as `v[locale]`
     */
    public function recommendByIdentifier(string $identifier, string $type = 'PRODUCT', ?string $locale = null): RecommendationsResponse
    {
        $parameters = ['id' => $type . '-' . $identifier];
        if ($locale !== null) {
            $parameters['v'] = ['locale' => I18nResolver::normalizeLocale($locale)];
        }
        $query = http_build_query($parameters);

        $response = $this->apiClient->getJson(
            '/documents/recommendations?' . $query,
            []
        );

        return new RecommendationsResponse($response->getRawResponse(), $response->getStatusCode(), $locale);
    }
}
