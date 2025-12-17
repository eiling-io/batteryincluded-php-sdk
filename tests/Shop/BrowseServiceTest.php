<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Shop;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\Service\AbstractService;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Service\SyncService;
use BatteryIncludedSdk\Shop\BrowseResponse;
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;
use BatteryIncludedSdk\Shop\Extension\CodesExtension;
use BatteryIncludedSdk\Shop\Extension\PromotionsExtension;
use BatteryIncludedSdk\Shop\Extension\RedirectsExtension;
use BatteryIncludedSdk\Shop\Extension\TeaserExtension;
use BatteryIncludedSdk\Shop\FacetCategoryDto;
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
#[CoversClass(CodesExtension::class)]
#[CoversClass(PromotionsExtension::class)]
#[CoversClass(RedirectsExtension::class)]
#[CoversClass(TeaserExtension::class)]
#[CoversClass(Response::class)]
#[UsesClass(CategoryDto::class)]
#[UsesClass(FacetDto::class)]
#[UsesClass(FacetRangeDto::class)]
#[UsesClass(FacetRatingDto::class)]
#[UsesClass(FacetSelectDto::class)]
#[UsesClass(FacetValueDto::class)]
#[UsesClass(FacetCategoryDto::class)]
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

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(720, $result->getBody());
        $browseService = new BrowseService(Helper::getApiClient());
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->addFilter('_PRODUCT.properties.Speicherkapazität', '512GB');
        $searchStruct->setSort('_PRODUCT.price:asc');
        $searchStruct->addFilter('_PRODUCT.categories', 'Apple > iPhone > iPhone 18 Pro');
        $searchStruct->addFilter('_PRODUCT.properties.Farbe', 'Schwarz');
        $searchStruct->addFilter('_PRODUCT.properties.Farbe', 'Blau');
        $searchStruct->setQuery('iPhone');
        $result = $browseService->browse($searchStruct);

        $this->assertContainsOnlyInstancesOf(FacetDto::class, $result->getFacets());

        $this->assertInstanceOf(BrowseResponse::class, $result);
        $this->assertCount(2, $result->getHits());

        $this->assertEquals(2, $result->getFound());

        $this->assertEquals($result->getPage(), 1);
        $this->assertEquals($result->getPages(), 1);
    }

    public function testBrowseMethodWithPresetAgainstLiveApi()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(720, $result->getBody());
        $browseService = new BrowseService(Helper::getApiClient());
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->addFilter('_PRODUCT.properties.Speicherkapazität', '512GB');
        $searchStruct->setPresetId('857e117c-3766-494d-a692-d7a23c384c33');
        $searchStruct->setSort('_PRODUCT.price:asc');
        $searchStruct->addFilter('_PRODUCT.categories', 'Apple > iPhone > iPhone 18 Pro');
        $searchStruct->addFilter('_PRODUCT.properties.Farbe', 'Schwarz');
        $searchStruct->addFilter('_PRODUCT.properties.Farbe', 'Blau');
        $searchStruct->setQuery('iPhone');
        $result = $browseService->browse($searchStruct);

        $this->assertContainsOnlyInstancesOf(FacetDto::class, $result->getFacets());

        $this->assertInstanceOf(BrowseResponse::class, $result);
        $this->assertCount(1, $result->getHits());

        $this->assertEquals(1, $result->getFound());

        $this->assertEquals($result->getPage(), 1);
        $this->assertEquals($result->getPages(), 1);
    }

    public function testBrowseMethodWithExtensionsAgainstLiveApi()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(720, $result->getBody());
        $browseService = new BrowseService(Helper::getApiClient());
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->setQuery('extension');
        $result = $browseService->browse($searchStruct);

        $this->assertContainsOnlyInstancesOf(FacetDto::class, $result->getFacets());

        $this->assertInstanceOf(BrowseResponse::class, $result);
        $this->assertCount(0, $result->getHits());
        $this->assertCount(4, $result->getAllExtensions());
        $this->assertCount(1, $result->getCodesExtensions());
        $this->assertCount(1, $result->getRedirectsExtensions());
        $this->assertCount(1, $result->getPromotionsExtensions());
        $this->assertCount(1, $result->getTeaserExtensions());

        $this->assertEquals(0, $result->getFound());

        $this->assertEquals(1, $result->getPage());
        $this->assertEquals(0, $result->getPages());
    }
}
