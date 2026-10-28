<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Recommendations;

use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Service\Response;

class RecommendationsResponse extends Response
{
    private array $recommendations;

    /**
     * @param string|null $locale requested locale, its translations are merged into the documents like in
     *                            BrowseResponse::getHits()
     */
    public function __construct(string $responseRaw, int $statusCode, private ?string $locale = null)
    {
        parent::__construct($responseRaw, $statusCode);

        $body = $this->getBody();

        if (empty($body)) {
            $this->recommendations = [
                'related' => [],
                'also' => [],
                'together' => [],
            ];

            return;
        }

        if ($this->locale !== null) {
            $body = I18nResolver::resolveHits($body, $this->locale);
        }

        foreach ($body as $recommendation) {
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
}
