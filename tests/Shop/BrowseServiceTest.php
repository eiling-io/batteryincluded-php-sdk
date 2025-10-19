<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Shop;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Product\CategoryDto;
use BatteryIncludedSdk\Product\ProductBaseDto;
use BatteryIncludedSdk\Product\ProductPropertyDto;
use BatteryIncludedSdk\Service\AbstractService;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Service\SyncService;
use BatteryIncludedSdk\Shop\BrowseResponse;
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;
use BatteryIncludedSdk\Shop\FacetDto;
use BatteryIncludedSdk\Shop\FacetRangeDto;
use BatteryIncludedSdk\Shop\FacetRatingDto;
use BatteryIncludedSdk\Shop\FacetSelectDto;
use BatteryIncludedSdk\Shop\FacetValueDto;
use BatteryIncludedSdkTests\Helper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BrowseService::class)]
#[CoversClass(BrowseResponse::class)]
#[CoversClass(ApiClient::class)]
#[CoversClass(CurlHttpClient::class)]
#[CoversClass(BrowseSearchStruct::class)]
#[CoversClass(Response::class)]
#[UsesClass(CategoryDto::class)]
#[UsesClass(FacetDto::class)]
#[UsesClass(FacetRangeDto::class)]
#[UsesClass(FacetRatingDto::class)]
#[UsesClass(FacetSelectDto::class)]
#[UsesClass(FacetValueDto::class)]
#[UsesClass(ProductBaseDto::class)]
#[UsesClass(ProductPropertyDto::class)]
#[UsesClass(AbstractService::class)]
#[UsesClass(SyncService::class)]
class BrowseServiceTest extends TestCase
{
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
        $searchStruct->addFilter('categories', 'Apple > iPhone > iPhone 18 Pro');
        $searchStruct->addFilter('properties.Farbe', 'Schwarz');
        $searchStruct->addFilter('properties.Farbe', 'Blau');
        $searchStruct->setQuery('iPhone');
        $result = $browseService->browse($searchStruct);

        $this->assertContainsOnlyInstancesOf(FacetDto::class, $result->getFacets());

        $this->assertInstanceOf(BrowseResponse::class, $result);
        $this->assertCount(2, $result->getHits());

        $this->assertEquals(2, $result->getFound());

        $this->assertEquals($result->getPage(), 1);
        $this->assertEquals($result->getPages(), 1);
    }
}
