<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\CartRecommendations;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Recommendations\RecommendationsService;

class CartRecommendationsService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    /**
     * @param array       $f            filters, translated fields without locale, e.g. `_PRODUCT_i18n.categories`
     * @param string|null $locale       locale of the translated fields, e.g. `de_DE`, sent as `v[locale]`
     * @param string|null $availability scope of `_PRODUCT_availability`, e.g. `ch`, sent as `v[availability]`
     */
    public function recommendByIdentifiers(
        array $identifiers,
        array $f = [],
        string $type = 'PRODUCT',
        ?string $locale = null,
        ?string $availability = null,
    ): CartRecommendationsResponse {
        $ids = array_map(static function ($id) use ($type) {
            return mb_strtoupper($type) . '-' . $id;
        }, $identifiers);
        $parameters = compact('ids', 'f');
        $variables = RecommendationsService::buildVariables($locale, $availability);
        if ($variables !== []) {
            $parameters['v'] = $variables;
        }
        $query = http_build_query($parameters);

        $response = $this->apiClient->getJson('/documents/recommendations/cart?' . $query);

        return new CartRecommendationsResponse($response->getRawResponse(), $response->getStatusCode(), $locale, $availability);
    }
}
