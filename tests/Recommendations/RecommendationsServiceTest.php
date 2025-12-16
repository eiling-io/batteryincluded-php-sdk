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
        $expectedData = [
            ['id' => 1, 'name' => 'Empfehlung 1'],
            ['id' => 2, 'name' => 'Empfehlung 2'],
        ];
        $jsonResponse = json_encode($expectedData);

        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getRawResponse')->willReturn($jsonResponse);

        $mockApiClient = $this->createMock(ApiClient::class);
        $mockApiClient->method('getJson')->willReturn($mockResponse);

        $service = new RecommendationsService($mockApiClient);
        $result = $service->recommendByIdentifier('240');

        $this->assertInstanceOf(RecommendationsResponse::class, $result);
        $this->assertSame($expectedData, $result->getRecommendations());
    }
}
