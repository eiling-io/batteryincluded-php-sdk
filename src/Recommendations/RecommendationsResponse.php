<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Recommendations;

use BatteryIncludedSdk\Service\Response;

class RecommendationsResponse extends Response
{
    private array $recommendations = [];

    public function __construct(string $responseRaw)
    {
        parent::__construct($responseRaw);
        foreach ($this->getBody() as $recommendation) {
            $this->recommendations[$recommendation['type']][] = $recommendation;
        }
    }

    public function getRecommendations(): array
    {
        return $this->recommendations;
    }
}
