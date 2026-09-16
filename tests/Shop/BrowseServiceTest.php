<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Shop;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\AbstractAvailability;
use BatteryIncludedSdk\Dto\AbstractTranslation;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductAvailability;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\Dto\ProductTranslation;
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
#[UsesClass(ProductTranslation::class)]
#[UsesClass(AbstractTranslation::class)]
#[UsesClass(ProductAvailability::class)]
#[UsesClass(AbstractAvailability::class)]
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
        $searchStruct->setLocale('de');
        $searchStruct->addFilter('_i18n._PRODUCT.properties.Storage', '512GB');
        $searchStruct->setSort('_PRODUCT.price:asc');
        $searchStruct->addFilter('_i18n._PRODUCT.categories', 'Apple > iPhone > iPhone 18 Pro');
        $searchStruct->addFilter('_i18n._PRODUCT.properties.Color', 'Schwarz');
        $searchStruct->addFilter('_i18n._PRODUCT.properties.Color', 'Blau');
        $searchStruct->setQuery('iPhone');
        $result = $browseService->browse($searchStruct);

        $this->assertContainsOnlyInstancesOf(FacetDto::class, $result->getFacets());

        $this->assertInstanceOf(BrowseResponse::class, $result);
        $this->assertCount(2, $result->getHits());

        $this->assertEquals(2, $result->getFound());

        $this->assertEquals($result->getPage(), 1);
        $this->assertEquals($result->getPages(), 1);
    }

    /**
     * Documents the expected en-locale behaviour: filtering on the English property value should
     * return English content. Currently fails against the live API - see the "Backend availability"
     * note in the README's Localization section: requesting v[locale]=en still resolves _i18n to the
     * de content and _i18n.en._PRODUCT is not searchable at all, regardless of the requested locale.
     * This looks like a BatteryIncluded-side locale/field-mapping config issue for this collection,
     * not an SDK bug - the "en" translation is present in the synced document (verified via id lookup),
     * it just isn't resolved/indexed for search yet.
     */
    public function testBrowseMethodWithEnglishLocaleAgainstLiveApi()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(720, $result->getBody());

        $browseService = new BrowseService(Helper::getApiClient());
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->setLocale('en');
        $searchStruct->addFilter('_i18n._PRODUCT.properties.Color', 'Blue');
        $result = $browseService->browse($searchStruct);

        $this->assertGreaterThan(0, $result->getFound());

        foreach ($result->getHits() as $hit) {
            $translation = $hit['document']['_i18n']['_PRODUCT'];
            $this->assertStringContainsString('Blue', $translation['name']);
            $this->assertStringContainsString('Color: Blue', $translation['description']);
        }
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
        $searchStruct->setLocale('de');
        $searchStruct->addFilter('_i18n._PRODUCT.properties.Storage', '512GB');
        $searchStruct->setPresetId('857e117c-3766-494d-a692-d7a23c384c33');
        $searchStruct->setSort('_PRODUCT.price:asc');
        $searchStruct->addFilter('_i18n._PRODUCT.categories', 'Apple > iPhone > iPhone 18 Pro');
        $searchStruct->addFilter('_i18n._PRODUCT.properties.Color', 'Schwarz');
        $searchStruct->addFilter('_i18n._PRODUCT.properties.Color', 'Blau');
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
