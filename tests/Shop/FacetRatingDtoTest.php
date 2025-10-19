<?php

declare(strict_types=1);

namespace Shop;

use BatteryIncludedSdk\Shop\FacetDto;
use BatteryIncludedSdk\Shop\FacetRatingDto;
use BatteryIncludedSdk\Shop\FacetValueDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FacetDto::class)]
#[CoversClass(FacetRatingDto::class)]
#[CoversClass(FacetValueDto::class)]
class FacetRatingDtoTest extends TestCase
{
    public function testFacetDtoInitialization()
    {
        $data = [
            'counts' => [
                ['count' => 20, 'value' => 'Rosa'],
                ['count' => 15, 'value' => 'Blau'],
            ],
            'field_name' => 'properties.Farbe',
            'stats' => ['total_values' => 2],
            'type' => 'select',
            'field_label' => 'Farbe',
            'field_unit' => '',
        ];
        $appliedFilterValues = ['properties.Farbe' => ['Rosa']];

        $facetDto = new FacetRatingDto($data, $appliedFilterValues);

        $this->assertEquals('Farbe', $facetDto->getFieldLabel());
        $this->assertEquals('', $facetDto->getFieldUnit());
        $this->assertEquals('properties.Farbe', $facetDto->getFieldName());
        $this->assertEquals(['total_values' => 2], $facetDto->getStats());
        $this->assertTrue($facetDto->isChecked());

        $values = $facetDto->getRatings();
        $this->assertArrayHasKey('Rosa', $values);
        $this->assertArrayHasKey('Blau', $values);
        $this->assertCount(2, $values);
    }

    public function testSetChecked()
    {
        $data = [
            'counts' => [],
            'field_name' => 'properties.Farbe',
            'stats' => [],
            'type' => 'select',
            'field_label' => '',
            'field_unit' => '',
        ];

        $facetDto = new FacetRatingDto($data);
        $this->assertFalse($facetDto->isChecked());

        $this->assertEquals('properties.Farbe', $facetDto->getFieldLabel());

        $facetDto->setChecked(true);
        $this->assertTrue($facetDto->isChecked());
    }
}
