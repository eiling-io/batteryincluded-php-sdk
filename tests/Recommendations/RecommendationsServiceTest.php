<?php

declare(strict_types=1);

namespace Recommendations;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Recommendations\RecommendationsResponse;
use BatteryIncludedSdk\Recommendations\RecommendationsService;
use BatteryIncludedSdk\Service\AbstractService;
use BatteryIncludedSdk\Service\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecommendationsResponse::class)]
#[CoversClass(RecommendationsService::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(CurlHttpClient::class)]
#[UsesClass(Response::class)]
#[UsesClass(AbstractService::class)]
class RecommendationsServiceTest extends TestCase
{
    public function testRecommendByIdentifierReturnsRecommendationsResponse()
    {
        $rawData = [
            ['type' => 'together', 'id' => 1, 'name' => 'Zusammen gekauft 1'],
            ['type' => 'together', 'id' => 2, 'name' => 'Zusammen gekauft 2'],
            ['type' => 'also', 'id' => 3, 'name' => 'Auch gekauft 1'],
            ['type' => 'also', 'id' => 4, 'name' => 'Auch gekauft 2'],
            ['type' => 'related', 'id' => 5, 'name' => 'Verknüpft 1'],
            ['type' => 'related', 'id' => 6, 'name' => 'Verknüpft 2'],
        ];
        $jsonResponse = json_encode($rawData);

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getRawResponse')->willReturn($jsonResponse);

        $mockApiClient = $this->createMock(ApiClient::class);
        $mockApiClient->method('getJson')->willReturn($mockResponse);

        $service = new RecommendationsService($mockApiClient);
        $result = $service->recommendByIdentifier('720');

        $this->assertInstanceOf(RecommendationsResponse::class, $result);
        $recommendations = $result->getRecommendations();
        $this->assertCount(2, $recommendations['together']);
        $this->assertCount(2, $recommendations['also']);
        $this->assertCount(2, $recommendations['related']);
    }
}
