<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Product;

use BatteryIncludedSdk\Product\ProductPropertyDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductPropertyDto::class)]
class ProductPropertyDtoTest extends TestCase
{
    public function testAddPropertyAndJsonSerialize()
    {
        $dto = new ProductPropertyDto();

        $dto->addProperty('Farbe', 'Rot');
        $dto->addProperty('Farbe', 'Blau');
        $dto->addProperty('Größe', 'L');

        $expected = [
            [
                'Farbe' => ['Rot', 'Blau'],
                'Größe' => ['L'],
            ],
        ];

        $this->assertSame($expected, $dto->jsonSerialize());
        $this->assertSame($expected, json_decode(json_encode($dto), true));
    }

    public function testEmptyProperties()
    {
        $dto = new ProductPropertyDto();
        $this->assertSame([[]], $dto->jsonSerialize());
    }
}
