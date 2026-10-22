<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Dto;

use BatteryIncludedSdk\Dto\AbstractTranslatableDto;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\I18n\I18nResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProductBaseDto::class)]
#[CoversClass(AbstractTranslatableDto::class)]
#[UsesClass(ProductPropertyDto::class)]
#[UsesClass(I18nResolver::class)]
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

    public function testTranslations()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $this->assertNull($dto->getTranslations());

        $this->assertSame($dto, $dto->addTranslation('DE', 'manufacture', 'Hersteller DE'));
        $dto->addTranslation('en', 'manufacture', 'Manufacturer EN');
        $dto->addTranslation('nl_NL', 'manufacture', 'Fabrikant NL');

        $this->assertSame([
            'de' => ['manufacture' => 'Hersteller DE'],
            'en' => ['manufacture' => 'Manufacturer EN'],
            'nl-nl' => ['manufacture' => 'Fabrikant NL'],
        ], $dto->getTranslations());
        $this->assertSame(['manufacture' => 'Manufacturer EN'], $dto->getTranslation('EN'));
        $this->assertNull($dto->getTranslation('fr'));
    }

    public function testTranslationOfUnknownFieldThrows()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');

        $this->expectException(\InvalidArgumentException::class);
        $dto->addTranslation('de', 'nmae', 'Typo');
    }

    public function testPropertyTranslationsKeepTheSameShapeAsProperties()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->setProperties((new ProductPropertyDto())->addProperty('Farbe', 'Rot'));

        $dto->addPropertyTranslation('de', 'Farbe', 'Rot');
        $dto->addPropertyTranslation('de', 'Farbe', 'Blau');
        $dto->addPropertyTranslation('en', 'Color', 'Red');
        $dto->addPropertyTranslation('en', 'Color.', 'Blue');

        $json = json_decode(json_encode($dto), true);

        $this->assertSame([['Farbe' => ['Rot']]], $json['_PRODUCT']['properties']);
        $this->assertSame([
            'de' => ['properties' => [['Farbe' => ['Rot', 'Blau']]]],
            'en' => ['properties' => [['Color' => ['Red', 'Blue']]]],
        ], $json['_PRODUCT_i18n']);
    }

    public function testCategoryTranslations()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');

        $catDe = $this->createMock(CategoryDto::class);
        $catDe->method('jsonSerialize')->willReturn(['Elektronik', 'Elektronik > Handys']);
        $dto->addCategoryTranslation('de', $catDe);
        $dto->addCategoryTranslation('de', $catDe);

        $catEn = $this->createMock(CategoryDto::class);
        $catEn->method('jsonSerialize')->willReturn(['Electronics', 'Electronics > Phones']);
        $dto->addCategoryTranslation('en', $catEn);

        $this->assertSame([
            'de' => ['categories' => ['Elektronik', 'Elektronik > Handys']],
            'en' => ['categories' => ['Electronics', 'Electronics > Phones']],
        ], $dto->getTranslations());
    }

    public function testJsonSerializeIncludesTranslations()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->setId('1');
        $dto->setManufacture('Manufacturer');
        $dto->addTranslation('de', 'manufacture', 'Hersteller');

        $this->assertSame([
            'id' => 'PRODUCT-1',
            'type' => 'PRODUCT',
            '_PRODUCT_i18n' => [
                'de' => ['manufacture' => 'Hersteller'],
            ],
            '_PRODUCT' => [
                'id' => '1',
                'manufacture' => 'Manufacturer',
            ],
        ], $dto->jsonSerialize());
    }

    public function testExportedTranslationsCanBeResolved()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->setId('1');
        $dto->setName('iPhone Blau');
        $dto->setDescription('Beschreibung');
        $dto->addTranslation('en', 'name', 'iPhone Blue');

        $document = json_decode(json_encode($dto), true);

        $this->assertSame(
            ['id' => '1', 'name' => 'iPhone Blue', 'description' => 'Beschreibung'],
            I18nResolver::resolveDocument($document, 'en')['_PRODUCT']
        );
        $this->assertSame(
            ['id' => '1', 'name' => 'iPhone Blau', 'description' => 'Beschreibung'],
            I18nResolver::resolveDocument($document, 'de')['_PRODUCT']
        );
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
                'name' => 'Name',
                'price' => 1.5,
                'categories' => ['A'],
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
                'name' => 'Name',
                'description' => null,
                'ordernumber' => null,
                'manufacture' => null,
                'manufactureNumber' => null,
                'ean' => null,
                'imageUrl' => null,
                'shopUrl' => null,
                'price' => 1.5,
                'instock' => null,
                'rating' => null,
                'categories' => null,
                'properties' => null,
            ],
        ];

        $this->assertSame($expected, $dto->jsonSerialize());
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
