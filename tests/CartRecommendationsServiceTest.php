<?php

declare(strict_types=1);

namespace CartRecommendations;

use BatteryIncludedSdk\CartRecommendations\CartRecommendationsResponse;
use BatteryIncludedSdk\CartRecommendations\CartRecommendationsService;
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Service\Response;
use PHPUnit\Framework\TestCase;

class CartRecommendationsServiceTest extends TestCase
{
    public function testRecommendByIdentifiersBuildsCorrectQueryString()
    {
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getRawResponse')->willReturn('{}');
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockApiClient = $this->createMock(ApiClient::class);
        $mockApiClient->expects($this->once())
            ->method('getJson')
            ->with(
                $this->callback(function($url) {
                    return str_contains($url, 'ids%5B0%5D=PRODUCT-1') && str_contains($url, 'ids%5B1%5D=PRODUCT-2');
                }),
                []
            )
            ->willReturn($mockResponse);

        $service = new CartRecommendationsService($mockApiClient);
        $response = $service->recommendByIdentifiers(['1', '2']);
        $this->assertInstanceOf(CartRecommendationsResponse::class, $response);
    }
}

