<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Recommendations;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Service\Response;

class RecommendationsResponse extends Response
{
    private array $recommendations = [
        'related' => [],
        'also' => [],
        'together' => [],
    ];

    /**
     * @param string|null $locale       requested locale, its translations are merged into the documents like in
     *                                  BrowseResponse::getHits()
     * @param string|null $availability requested availability scope, merged after the translations
     */
    public function __construct(
        string $responseRaw,
        int $statusCode,
        private ?string $locale = null,
        private ?string $availability = null,
    ) {
        parent::__construct($responseRaw, $statusCode);

        $body = $this->getBody();

        if ($this->locale !== null) {
            $body = I18nResolver::resolveHits($body, $this->locale);
        }

        if ($this->availability !== null) {
            $body = AvailabilityResolver::resolveHits($body, $this->availability);
        }

        foreach ($body as $recommendation) {
            if (!is_array($recommendation) || !is_string($recommendation['type'] ?? null)) {
                continue;
            }

            $this->recommendations[$recommendation['type']][] = $recommendation;
        }
    }

    public function getRecommendations(): array
    {
        return $this->recommendations;
    }

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }
}
