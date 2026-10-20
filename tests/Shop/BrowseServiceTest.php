<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Shop;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\I18n\I18nResolver;
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
#[UsesClass(I18nResolver::class)]
class BrowseServiceTest extends TestCase
{
    public function testBrowseSendsDefaultLocale()
    {
        $this->assertBrowseUrlContains(new BrowseSearchStruct(), 'v%5Blocale%5D=de');
    }

    public function testBrowseSendsCustomLocale()
    {
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->setLocale('EN');
        $this->assertBrowseUrlContains($searchStruct, 'v%5Blocale%5D=en');
    }

    public function testBrowseLocaleVariableOverridesLocale()
    {
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->addVariable('locale', 'FR');
        $this->assertBrowseUrlContains($searchStruct, 'v%5Blocale%5D=fr');
    }

    public function testHitsAreResolvedWithRequestedLocale()
    {
        $raw = json_encode([
            'extensions' => [],
            'hits' => [[
                'document' => [
                    'id' => 'PRODUCT-1',
                    'type' => 'PRODUCT',
                    '_PRODUCT' => [
                        'name' => 'iPhone Blau',
                        'price' => 999.5,
                    ],
                    // the gateway delivers the node already resolved for the requested locale
                    '_PRODUCT_i18n' => ['name' => 'iPhone Blue'],
                ],
            ]],
        ]);

        $searchStruct = new BrowseSearchStruct();
        $searchStruct->setLocale('EN');
        $response = new BrowseResponse($raw, $searchStruct);

        $this->assertSame('EN', $response->getLocale());
        $this->assertSame(
            ['name' => 'iPhone Blue', 'price' => 999.5],
            $response->getHits()[0]['document']['_PRODUCT']
        );
        $this->assertSame(
            ['name' => 'iPhone Blue'],
            $response->getRawHits()[0]['document']['_PRODUCT_i18n']
        );
    }

    private function assertBrowseUrlContains(BrowseSearchStruct $searchStruct, string $expected): void
    {
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getRawResponse')->willReturn('{"extensions":[]}');

        $mockApiClient = $this->createMock(ApiClient::class);
        $mockApiClient->expects($this->once())
            ->method('getJson')
            ->with(
                $this->callback(fn ($url) => str_contains($url, $expected)),
                []
            )
            ->willReturn($mockResponse);

        $service = new BrowseService($mockApiClient);
        $this->assertInstanceOf(BrowseResponse::class, $service->browse($searchStruct));
    }

    public function testBrowseMethodAgainstLiveApi()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(720, $result->getBody());
        $browseService = new BrowseService(Helper::getApiClient());
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->addFilter('_PRODUCT.properties.Storage', '512GB');
        $searchStruct->setSort('_PRODUCT.price:asc');
        $searchStruct->addFilter('_PRODUCT.categories', 'BatteryIncluded > Smartphone > Smartphone 18 Pro');
        $searchStruct->addFilter('_PRODUCT.properties.Color', 'Schwarz');
        $searchStruct->addFilter('_PRODUCT.properties.Color', 'Blau');
        $searchStruct->setQuery('Smartphone');
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
        $searchStruct->addFilter('_PRODUCT.properties.Storage', '512GB');
        $searchStruct->setPresetId('857e117c-3766-494d-a692-d7a23c384c33');
        $searchStruct->setSort('_PRODUCT.price:asc');
        $searchStruct->addFilter('_PRODUCT.categories', 'BatteryIncluded > Smartphone > Smartphone 18 Pro');
        $searchStruct->addFilter('_PRODUCT.properties.Color', 'Schwarz');
        $searchStruct->addFilter('_PRODUCT.properties.Color', 'Blau');
        $searchStruct->setQuery('Smartphone');
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

    public function testBrowseMethodWithVariablesAgainstLiveApi()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(720, $result->getBody());
        $browseService = new BrowseService(Helper::getApiClient());
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->setQuery('extension');
        $searchStruct->addVariable('locale', 'DE');
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
