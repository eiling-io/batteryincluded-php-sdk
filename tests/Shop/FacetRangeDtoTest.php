<?php

declare(strict_types=1);

namespace Shop;

use BatteryIncludedSdk\Shop\FacetDto;
use BatteryIncludedSdk\Shop\FacetRangeDto;
use BatteryIncludedSdk\Shop\FacetValueDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FacetDto::class)]
#[CoversClass(FacetRangeDto::class)]
#[CoversClass(FacetValueDto::class)]
class FacetRangeDtoTest extends TestCase
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

        $facetDto = new FacetDto($data, $appliedFilterValues);

        $this->assertEquals('Farbe', $facetDto->getFieldLabel());
        $this->assertEquals('', $facetDto->getFieldUnit());
        $this->assertEquals('properties.Farbe', $facetDto->getFieldName());
        $this->assertEquals(['total_values' => 2], $facetDto->getStats());
        $this->assertTrue($facetDto->isChecked());
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

        $facetDto = new FacetRangeDto($data);
        $this->assertFalse($facetDto->isChecked());

        $this->assertEquals('properties.Farbe', $facetDto->getFieldLabel());

        $facetDto->setChecked(true);
        $this->assertTrue($facetDto->isChecked());
    }

    public function testRangeFacetMinMax()
    {
        $data = [
            'field_name' => 'properties.Preis',
            'stats' => ['min' => 10.5, 'max' => 99.9],
            'type' => 'range',
            'field_label' => 'Preis',
            'field_unit' => '€',
        ];
        $facetDto = new FacetRangeDto($data);

        $this->assertEquals(10.5, $facetDto->getMin());
        $this->assertEquals(99.9, $facetDto->getMax());
        $this->assertEquals(10.5, $facetDto->getSelectedMin());
        $this->assertEquals(99.9, $facetDto->getSelectedMax());
    }

    public function testRangeFacetSelectedMinMax()
    {
        $data = [
            'field_name' => 'properties.Preis',
            'stats' => ['min' => 5.0, 'max' => 50.0],
            'type' => 'range',
            'field_label' => 'Preis',
            'field_unit' => '€',
        ];
        $appliedFilterValues = [
            'properties.Preis' => [
                'from' => 12.34,
                'till' => 45.67,
            ],
        ];
        $facetDto = new FacetRangeDto($data, $appliedFilterValues);

        $this->assertEquals(5.0, $facetDto->getMin());
        $this->assertEquals(50.0, $facetDto->getMax());
        $this->assertEquals(12.34, $facetDto->getSelectedMin());
        $this->assertEquals(45.67, $facetDto->getSelectedMax());
    }
}
