<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Recommendations;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\I18n\I18nResolver;

class RecommendationsService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    /**
     * @param string|null $locale       locale of the translated fields, e.g. `de_DE`, sent as `v[locale]`
     * @param string|null $availability scope of `_PRODUCT_availability`, e.g. `ch`, sent as `v[availability]`
     */
    public function recommendByIdentifier(
        string $identifier,
        string $type = 'PRODUCT',
        ?string $locale = null,
        ?string $availability = null,
    ): RecommendationsResponse {
        $parameters = ['id' => mb_strtoupper($type) . '-' . $identifier];
        $variables = self::buildVariables($locale, $availability);
        if ($variables !== []) {
            $parameters['v'] = $variables;
        }
        $query = http_build_query($parameters);

        $response = $this->apiClient->getJson('/documents/recommendations?' . $query);

        return new RecommendationsResponse($response->getRawResponse(), $response->getStatusCode(), $locale, $availability);
    }

    /**
     * Gateway variables `v[locale]` and `v[availability]`, normalized and only if set.
     *
     * @internal shared with the CartRecommendationsService
     */
    public static function buildVariables(?string $locale, ?string $availability): array
    {
        $variables = [];
        if ($locale !== null) {
            $variables['locale'] = I18nResolver::normalizeLocale($locale);
        }
        if ($availability !== null) {
            $variables[AvailabilityResolver::VARIABLE] = AvailabilityResolver::normalizeScope($availability);
        }

        return $variables;
    }
}
