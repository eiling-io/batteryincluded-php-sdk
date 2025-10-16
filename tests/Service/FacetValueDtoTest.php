<?php

declare(strict_types=1);

namespace Service;

use BatteryIncludedSdk\Service\FacetValueDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FacetValueDto::class)]
class FacetValueDtoTest extends TestCase
{
    public function testInitialization()
    {
        $facetValueDto = new FacetValueDto(['value' => 'Rosa', 'count' => 20]);

        $this->assertEquals('Rosa', $facetValueDto->getName());
        $this->assertEquals(20, $facetValueDto->getCount());
        $this->assertFalse($facetValueDto->isChecked());
    }

    public function testSetChecked()
    {
        $facetValueDto = new FacetValueDto(['value' => 'Blau', 'count' => 15], ['Blau']);

        $this->assertTrue($facetValueDto->isChecked());
    }
}
