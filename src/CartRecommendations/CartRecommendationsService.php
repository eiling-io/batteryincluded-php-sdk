<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\CartRecommendations;

use BatteryIncludedSdk\Client\ApiClient;

class CartRecommendationsService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    public function recommendByIdentifiers(array $identifiers, string $type = 'PRODUCT'): CartRecommendationsResponse
    {
        $ids = array_map(static function ($id) use ($type) {
            return $type . '-' . $id;
        }, $identifiers);
        $query = http_build_query(compact('ids'));

        $response = $this->apiClient->getJson(
            '/documents/recommendations/cart?' . $query,
            []
        );

        return new CartRecommendationsResponse($response->getRawResponse(), $response->getStatusCode());
    }
}
