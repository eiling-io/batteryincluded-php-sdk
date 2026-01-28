<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Recommendations;

use BatteryIncludedSdk\Service\Response;

class RecommendationsResponse extends Response
{
    private array $recommendations;

    public function __construct(string $responseRaw, int $statusCode)
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

        foreach ($body as $recommendation) {
            $this->recommendations[$recommendation['type']][] = $recommendation;
        }
    }

    public function getRecommendations(): array
    {
        return $this->recommendations;
    }
}
