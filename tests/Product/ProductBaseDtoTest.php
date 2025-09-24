<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Product;

use BatteryIncludedSdk\Product\CategoryDto;
use BatteryIncludedSdk\Product\ProductBaseDto;
use BatteryIncludedSdk\Product\ProductPropertyDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductBaseDto::class)]
class ProductBaseDtoTest extends TestCase
{
    public function testSettersAndGetters()
    {
        $dto = new ProductBaseDto();

        $dto->setId('123');
        $dto->setName('Testprodukt');
        $dto->setDescription('Beschreibung');
        $dto->setOrdernumber('A1');
        $dto->setManufacture('Hersteller');
        $dto->setManufactureNumber('MN-1');
        $dto->setEan('1234567890123');
        $dto->setImageUrl('http://img');
        $dto->setShopUrl('http://shop');
        $dto->setPrice(9.99);
        $dto->setInstock(5);

        $this->assertSame('123', $dto->getId());
        $this->assertSame('Testprodukt', $dto->getName());
        $this->assertSame('Beschreibung', $dto->getDescription());
        $this->assertSame('A1', $dto->getOrdernumber());
        $this->assertSame('Hersteller', $dto->getManufacture());
        $this->assertSame('MN-1', $dto->getManufactureNumber());
        $this->assertSame('1234567890123', $dto->getEan());
        $this->assertSame('http://img', $dto->getImageUrl());
        $this->assertSame('http://shop', $dto->getShopUrl());
        $this->assertSame(9.99, $dto->getPrice());
        $this->assertSame(5, $dto->getInstock());
    }

    public function testCategories()
    {
        $dto = new ProductBaseDto();
        $this->assertNull($dto->getCategories());

        $cat = $this->createMock(CategoryDto::class);
        $cat->method('jsonSerialize')->willReturn(['A', 'A > B']);

        $dto->addCategory($cat);
        $this->assertSame(['A', 'A > B'], $dto->getCategories());
    }

    public function testProperties()
    {
        $dto = new ProductBaseDto();
        $this->assertNull($dto->getProperties());

        $prop = $this->createMock(ProductPropertyDto::class);
        $dto->setProperties($prop);
        $this->assertSame($prop, $dto->getProperties());
    }

    public function testJsonSerialize()
    {
        $dto = new ProductBaseDto();
        $dto->setId('1');
        $dto->setName('Name');
        $dto->setPrice(1.5);

        $cat = $this->createMock(CategoryDto::class);
        $cat->method('jsonSerialize')->willReturn(['A']);
        $dto->addCategory($cat);

        $expected = [
            'id' => '1',
            'name' => 'Name',
            'price' => 1.5,
            'categories' => ['A'],
        ];

        $this->assertSame($expected, $dto->jsonSerialize());
    }
}
