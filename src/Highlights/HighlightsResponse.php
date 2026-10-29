<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Highlights;

use BatteryIncludedSdk\Service\Response;

class HighlightsResponse extends Response
{
    private array $querySuggestions;
    private array $searches;

    public function __construct(string $responseRaw, int $statusCode)
    {
        parent::__construct($responseRaw, $statusCode);
        $body = $this->getBody();
        $this->querySuggestions = is_array($body['querySuggestions'] ?? null) ? $body['querySuggestions'] : [];
        $this->searches = is_array($body['searches'] ?? null) ? $body['searches'] : [];
    }

    public function getAll(): array
    {
        return [
            'searches' => $this->getSearches(),
            'querySuggestions' => $this->getQuerySuggestions(),
        ];
    }

    public function getSearches(): array
    {
        return $this->searches;
    }

    public function getQuerySuggestions(): array
    {
        return $this->querySuggestions;
    }
}
