<?php

declare(strict_types=1);

namespace Service;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Service\BrowseSearchStruct;
use BatteryIncludedSdk\Service\BrowseService;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Service\SyncService;
use BatteryIncludedSdkTests\Helper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BrowseService::class)]
#[CoversClass(ApiClient::class)]
#[CoversClass(CurlHttpClient::class)]
#[CoversClass(BrowseSearchStruct::class)]
#[CoversClass(Response::class)]
class BrowseServiceTest extends TestCase
{
    public function testBrowseCallsApiClientWithCorrectParameters()
    {
        $searchStruct = $this->createMock(BrowseSearchStruct::class);
        $searchStruct->method('getQuery')->willReturn('test');
        $searchStruct->method('getFilters')->willReturn(['color' => ['red']]);
        $searchStruct->method('getPage')->willReturn(2);
        $searchStruct->method('getPerPage')->willReturn(10);
        $searchStruct->method('getSort')->willReturn('name:asc');

        $expectedQuery = http_build_query([
            'q' => 'test',
            'f' => ['color' => ['red']],
            'page' => 2,
            'per_page' => 10,
            'sort' => 'name:asc',
        ]);

        $expectedUrl = '/documents/browse?' . $expectedQuery;
        $expectedResponse = $this->createMock(Response::class);

        $apiClient = $this->createMock(ApiClient::class);
        $apiClient->expects($this->once())
            ->method('getJson')
            ->with($expectedUrl, [])
            ->willReturn($expectedResponse);

        $service = new BrowseService($apiClient);
        $result = $service->browse($searchStruct);

        $this->assertSame($expectedResponse, $result);
    }

    public function testBrowseMethodAgainstLiveApi()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyProducts(...$products);
        $this->assertCount(240, $result->getBody());
        $browseService = new BrowseService(Helper::getApiClient());
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->addFilter('properties.Speicherkapazität', '512GB');
        $searchStruct->setSort('price:asc');
        $searchStruct->addFilter('categories', 'Apple > iPhone > iPhone 20 Pro');
        $searchStruct->addFilter('properties.Farbe', 'Schwarz');
        $searchStruct->addFilter('properties.Farbe', 'Blau');
        $searchStruct->setQuery('iPhone');
        $result = $browseService->browse($searchStruct);

        $this->assertInstanceOf(Response::class, $result);
        $this->assertEquals($result->getBody()['found'], 2);
    }
}
