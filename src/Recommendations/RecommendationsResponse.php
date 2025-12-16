<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Recommendations;

use BatteryIncludedSdk\Service\Response;

class RecommendationsResponse extends Response
{
    public function __construct(string $responseRaw)
    {
        parent::__construct($responseRaw);
    }

    public function getRecommendations(): array
    {
        return $this->getBody();
    }
}
