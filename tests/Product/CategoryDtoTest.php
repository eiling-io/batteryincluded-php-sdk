<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Product;

use BatteryIncludedSdk\Product\CategoryDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CategoryDto::class)]
final class CategoryDtoTest extends TestCase
{
    public function testAddCategoryNodeAndJsonSerialize()
    {
        $dto = new CategoryDto();

        $dto->addCategoryNode('Elektronik');
        $dto->addCategoryNode('Computer');
        $dto->addCategoryNode('Laptops');

        $expected = [
            'Elektronik',
            'Elektronik > Computer',
            'Elektronik > Computer > Laptops',
        ];

        $this->assertSame($expected, $dto->jsonSerialize());
        $this->assertSame($expected, json_decode(json_encode($dto), true));
    }

    public function testAddCategoryNodeRemovesGreaterThanSign()
    {
        $dto = new CategoryDto();
        $dto->addCategoryNode('A > B');
        $dto->addCategoryNode('C');

        $expected = [
            'A  B',
            'A  B > C',
        ];

        $this->assertSame($expected, $dto->jsonSerialize());
    }
}
