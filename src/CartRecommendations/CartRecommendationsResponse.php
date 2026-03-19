<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\CartRecommendations;

use BatteryIncludedSdk\Service\Response;

class CartRecommendationsResponse extends Response
{
    public function __construct(string $responseRaw, int $statusCode)
    {
        parent::__construct($responseRaw, $statusCode);
    }
}
