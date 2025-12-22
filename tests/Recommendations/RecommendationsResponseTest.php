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
            ['type' => 'together', 'id' => 1, 'name' => 'Zusammen gekauft 1'],
            ['type' => 'together', 'id' => 2, 'name' => 'Zusammen gekauft 2'],
            ['type' => 'also', 'id' => 3, 'name' => 'Auch gekauft 1'],
            ['type' => 'also', 'id' => 4, 'name' => 'Auch gekauft 2'],
            ['type' => 'related', 'id' => 5, 'name' => 'Verknüpft 1'],
            ['type' => 'related', 'id' => 6, 'name' => 'Verknüpft 2'],
        ];
        $responseRaw = json_encode($data);

        $recommendations = (new RecommendationsResponse($responseRaw))->getRecommendations();

        $this->assertCount(2, $recommendations['together']);
        $this->assertCount(2, $recommendations['also']);
        $this->assertCount(2, $recommendations['related']);
    }
}
