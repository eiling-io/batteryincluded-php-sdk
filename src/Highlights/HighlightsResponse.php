<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Highlights;

use BatteryIncludedSdk\Service\Response;

class HighlightsResponse extends Response
{
    private $querySuggestions;
    private $searches;

    public function __construct(string $responseRaw)
    {
        parent::__construct($responseRaw);
        $this->querySuggestions = $this->getBody()['querySuggestions'] ?? [];
        $this->searches = $this->getBody()['searches'] ?? [];
    }

    public function getAll(): array
    {
        return [
            'searches' => $this->getSearches(),
            'querySuggestions' => $this->getQuerySuggestions(),
        ];
    }

    public function getSearches()
    {
        return $this->searches;
    }

    public function getQuerySuggestions()
    {
        return $this->querySuggestions;
    }
}
