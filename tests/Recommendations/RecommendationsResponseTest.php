<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Recommendations;

use BatteryIncludedSdk\Recommendations\RecommendationsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecommendationsResponse::class)]
class RecommendationsResponseTest extends TestCase
{
    public function testGetRecommendationsReturnsBody(): void
    {
        $data = [
            ['id' => 1, 'name' => 'Empfehlung 1'],
            ['id' => 2, 'name' => 'Empfehlung 2'],
        ];
        $responseRaw = json_encode($data);

        $response = new RecommendationsResponse($responseRaw);

        $this->assertSame($data, $response->getRecommendations());
    }
}
