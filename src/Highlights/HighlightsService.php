<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Highlights;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\I18n\I18nResolver;

class HighlightsService
{
    public function __construct(private ApiClient $apiClient)
    {
    }

    /**
     * @param string|null $locale locale of the translated query suggestions, e.g. `de_DE`, sent as `v[locale]`
     */
    public function getHighlights(?string $locale = null): HighlightsResponse
    {
        $query = $locale !== null ? '?' . http_build_query(['v' => ['locale' => I18nResolver::normalizeLocale($locale)]]) : '';

        $response = $this->apiClient->getJson(
            '/documents/highlights' . $query,
            []
        );

        return new HighlightsResponse($response->getRawResponse(), $response->getStatusCode());
    }
}
