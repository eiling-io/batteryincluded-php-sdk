<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Dto;

use BatteryIncludedSdk\Dto\AbstractAvailability;
use BatteryIncludedSdk\Dto\AbstractTranslation;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductAvailability;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\Dto\ProductTranslation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductBaseDto::class)]
#[UsesClass(ProductTranslation::class)]
#[UsesClass(AbstractTranslation::class)]
#[UsesClass(ProductAvailability::class)]
#[UsesClass(AbstractAvailability::class)]
class ProductBaseDtoTest extends TestCase
{
    public function testSettersAndGetters()
    {
        $dto = new ProductBaseDto('123', 'PRODUCT');

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
        $dto->setRating(2.5);
        $this->assertSame('123', $dto->getId());
        $this->assertSame('PRODUCT-123', $dto->getIdentifier());
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
        $this->assertSame(2.5, $dto->getRating());
    }

    public function testCategories()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $this->assertNull($dto->getCategories());

        $cat = $this->createMock(CategoryDto::class);
        $cat->method('jsonSerialize')->willReturn(['A', 'A > B']);

        $dto->addCategory($cat);
        $this->assertSame(['A', 'A > B'], $dto->getCategories());
    }

    public function testProperties()
    {
        $dto = new ProductBaseDto('id', 'PRODUCT');
        $this->assertNull($dto->getProperties());

        $prop = $this->createMock(ProductPropertyDto::class);
        $dto->setProperties($prop);
        $this->assertSame($prop, $dto->getProperties());
    }

    public function testJsonSerialize()
    {
        $id = '1';
        $type = 'PRODUCT';
        $dto = new ProductBaseDto($id, $type);
        $dto->setId($id);
        $dto->setName('Name');
        $dto->setPrice(1.5);

        $cat = $this->createMock(CategoryDto::class);
        $cat->method('jsonSerialize')->willReturn(['A']);
        $dto->addCategory($cat);

        $expected = [
            'id' => $type . '-' . $id,
            'type' => $type,
            '_' . $type => [
                'id' => '1',
                'price' => 1.5,
            ],
            '_i18n' => [
                'de' => [
                    '_PRODUCT' => [
                        'name' => 'Name',
                        'categories' => ['A'],
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $dto->jsonSerialize());
    }

    public function testJsonSerializeExportsNullValuesWhenEnabled()
    {
        $id = '1';
        $type = 'PRODUCT';
        $dto = new ProductBaseDto($id, $type);
        $dto->setId($id);
        $dto->setName('Name');
        $dto->setPrice(1.5);

        $this->assertSame($dto, $dto->exportNullValues());

        $expected = [
            'id' => $type . '-' . $id,
            'type' => $type,
            '_' . $type => [
                'id' => '1',
                'ordernumber' => null,
                'manufacture' => null,
                'manufactureNumber' => null,
                'ean' => null,
                'imageUrl' => null,
                'shopUrl' => null,
                'price' => 1.5,
                'instock' => null,
                'rating' => null,
            ],
            '_i18n' => [
                'de' => [
                    '_PRODUCT' => [
                        'name' => 'Name',
                        'description' => null,
                        'categories' => null,
                        'properties' => null,
                        'url' => null,
                    ],
                ],
            ],
        ];

        $this->assertSame($expected, $dto->jsonSerialize());
    }

    public function testJsonSerializeRoutesTranslatableFieldsToConfiguredLocale()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->locale('en');
        $dto->setId('1');
        $dto->setName('English name');
        $dto->setShopUrl('https://shop.example/product-1');
        $dto->addTranslation(new ProductTranslation(locale: 'de', name: 'Deutscher Name'));

        $json = $dto->jsonSerialize();

        // properties/categories stay their own translation-scoped fields, distinct from any structural data
        $this->assertSame(
            ['name' => 'English name', 'url' => 'https://shop.example/product-1'],
            $json['_i18n']['en']['_PRODUCT']
        );
        // the shop URL is identical for every locale here, so both translations inherit it without repeating it
        $this->assertSame(
            ['name' => 'Deutscher Name', 'url' => 'https://shop.example/product-1'],
            $json['_i18n']['de']['_PRODUCT']
        );
        $this->assertSame('https://shop.example/product-1', $json['_PRODUCT']['shopUrl']);
    }

    public function testJsonSerializeOmitsAvailabilityWhenNoneAdded()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->setId('1');

        $this->assertArrayNotHasKey('_availability', $dto->jsonSerialize());
    }

    public function testJsonSerializeIncludesPerMarketAvailability()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->setId('1');
        $dto->addAvailability(new ProductAvailability(market: 'de', active: true, instock: 14, price: 699.0));
        $dto->addAvailability(new ProductAvailability(market: 'ch', active: false));

        $json = $dto->jsonSerialize();

        $this->assertSame(
            ['active' => true, 'instock' => 14, 'price' => 699.0],
            $json['_availability']['de']['_PRODUCT']
        );
        $this->assertSame(['active' => false], $json['_availability']['ch']['_PRODUCT']);
    }

    public function testJsonSerializeFiltersNullValuesByDefault()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->setId('1');

        $this->assertSame(['id' => '1'], $dto->jsonSerialize()['_PRODUCT']);
    }

    public function testExportNullValuesCanBeDisabledAgain()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->setId('1');
        $dto->exportNullValues();
        $dto->exportNullValues(false);

        $this->assertSame(['id' => '1'], $dto->jsonSerialize()['_PRODUCT']);
    }
}
