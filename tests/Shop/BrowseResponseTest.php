<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Shop;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Shop\BrowseResponse;
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\FacetCategoryDto;
use BatteryIncludedSdk\Shop\FacetDto;
use BatteryIncludedSdk\Shop\FacetRangeDto;
use BatteryIncludedSdk\Shop\FacetSelectDto;
use BatteryIncludedSdk\Shop\FacetValueDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BrowseResponse::class)]
#[UsesClass(Response::class)]
#[UsesClass(BrowseSearchStruct::class)]
#[UsesClass(FacetDto::class)]
#[UsesClass(FacetCategoryDto::class)]
#[UsesClass(FacetRangeDto::class)]
#[UsesClass(FacetSelectDto::class)]
#[UsesClass(FacetValueDto::class)]
#[UsesClass(I18nResolver::class)]
#[UsesClass(AvailabilityResolver::class)]
class BrowseResponseTest extends TestCase
{
    public function testFacetWithoutCountsIsSkipped()
    {
        $response = $this->createResponse([
            'facet_counts' => [
                ['field_name' => '_PRODUCT.properties.Color', 'type' => 'select', 'counts' => [['value' => 'Red', 'count' => 3]]],
                // facet without hits, the api delivers it without type
                ['field_name' => '_PRODUCT.properties.Size', 'counts' => []],
                ['field_name' => '_PRODUCT.price', 'type' => 'range', 'stats' => ['min' => 1, 'max' => 99]],
            ],
        ]);

        $facets = $response->getFacets();

        $this->assertCount(2, $facets);
        $this->assertInstanceOf(FacetSelectDto::class, $facets[0]);
        $this->assertInstanceOf(FacetRangeDto::class, $facets[1]);
    }

    public function testCategoryFieldWithoutTypeBecomesCategoryFacet()
    {
        $response = $this->createResponse([
            'facet_counts' => [
                ['field_name' => '_PRODUCT.categories', 'counts' => []],
            ],
        ]);

        $facets = $response->getFacets();

        $this->assertCount(1, $facets);
        $this->assertInstanceOf(FacetCategoryDto::class, $facets[0]);
        $this->assertSame([], $facets[0]->getCategories());
    }

    public function testMissingFacetCountsAndExtensions()
    {
        $response = $this->createResponse([]);

        $this->assertSame([], $response->getFacets());
        $this->assertSame([], $response->getAllExtensions());
    }

    public function testFacetsOfOtherLocalesAreSkipped()
    {
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->setLocale('de_CH');
        $response = $this->createResponse([
            'facet_counts' => [
                ['field_name' => '_PRODUCT_i18n.properties.Color', 'type' => 'select', 'counts' => [['value' => 'Rot', 'count' => 3]]],
                ['field_name' => '_PRODUCT_i18n.en.properties.Color', 'type' => 'select', 'counts' => [['value' => 'Red', 'count' => 3]]],
                ['field_name' => '_PRODUCT_i18n.fr-ch.properties.Color', 'type' => 'select', 'counts' => [['value' => 'Rouge', 'count' => 3]]],
                // without a configured locale variable the gateway names the requested locale, it stays
                ['field_name' => '_PRODUCT_i18n.de-ch.properties.Size', 'type' => 'select', 'counts' => [['value' => 'L', 'count' => 3]]],
            ],
        ], $searchStruct);

        $this->assertSame(
            ['_PRODUCT_i18n.properties.Color', '_PRODUCT_i18n.de-ch.properties.Size'],
            array_map(static fn (FacetDto $facet) => $facet->getFieldName(), $response->getFacets())
        );
    }

    public function testTranslatedCategoryTreeIsDetected()
    {
        $response = $this->createResponse([
            'facet_counts' => [
                ['field_name' => '_PRODUCT.categories', 'type' => 'select', 'counts' => [['value' => 'Elektronik', 'count' => 3]]],
                ['field_name' => '_PRODUCT_i18n.categories', 'type' => 'select', 'counts' => [['value' => 'Electronics', 'count' => 3]]],
                ['field_name' => '_PRODUCT_i18n.de.categories', 'type' => 'select', 'counts' => [['value' => 'Elektronik', 'count' => 3]]],
            ],
        ]);

        $facets = $response->getFacets();

        $this->assertCount(2, $facets);
        $this->assertInstanceOf(FacetSelectDto::class, $facets[0]);
        $this->assertInstanceOf(FacetCategoryDto::class, $facets[1]);
        $this->assertSame('_PRODUCT_i18n.categories', $facets[1]->getFieldName());

        // an explicit field wins
        $this->assertInstanceOf(FacetCategoryDto::class, $response->getFacets('_PRODUCT.categories')[0]);
        $this->assertInstanceOf(FacetSelectDto::class, $response->getFacets('_PRODUCT.categories')[1]);
    }

    public function testStatusCodeIsKept()
    {
        $response = new BrowseResponse('{"found":3}', new BrowseSearchStruct(), 200);

        $this->assertTrue($response->isSuccessful());
        $this->assertSame(3, $response->getFound());
        $this->assertSame(1, $response->getPages());
    }

    private function createResponse(array $body, ?BrowseSearchStruct $searchStruct = null): BrowseResponse
    {
        // the default locale `DE` would hide the `_PRODUCT_i18n.de.*` facets
        $searchStruct ??= (new BrowseSearchStruct())->setLocale('en');

        return new BrowseResponse((string) json_encode($body), $searchStruct);
    }
}
