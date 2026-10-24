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

    private function createResponse(array $body): BrowseResponse
    {
        return new BrowseResponse(json_encode($body), new BrowseSearchStruct());
    }
}
