<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Dto;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\Dto\AbstractAvailabilityDto;
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
#[CoversClass(AbstractAvailabilityDto::class)]
#[UsesClass(ProductPropertyDto::class)]
#[UsesClass(CategoryDto::class)]
#[UsesClass(I18nResolver::class)]
#[CoversClass(AvailabilityResolver::class)]
class ProductBaseDtoTest extends TestCase
{
    public function testSettersAndGetters()
    {
        $dto = new ProductBaseDto('123', 'PRODUCT');

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
        $dto->setActive(false);
        $this->assertFalse($dto->getActive());
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

        $json = json_decode((string) json_encode($dto), true);

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
        $dto->setManufacture('Manufacturer');
        $dto->addTranslation('de', 'manufacture', 'Hersteller');

        $this->assertSame([
            'id' => 'PRODUCT-1',
            'type' => 'PRODUCT',
            '_PRODUCT_i18n' => [
                'de' => ['manufacture' => 'Hersteller'],
            ],
            '_PRODUCT' => [
                'manufacture' => 'Manufacturer',
            ],
        ], $dto->jsonSerialize());
    }

    public function testExportedTranslationsCanBeResolved()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->setName('iPhone Blau');
        $dto->setDescription('Beschreibung');
        $dto->addTranslation('en', 'name', 'iPhone Blue');

        $document = json_decode((string) json_encode($dto), true);

        $this->assertSame(
            ['name' => 'iPhone Blue', 'description' => 'Beschreibung'],
            I18nResolver::resolveDocument($document, 'en')['_PRODUCT']
        );
        $this->assertSame(
            ['name' => 'iPhone Blau', 'description' => 'Beschreibung'],
            I18nResolver::resolveDocument($document, 'de')['_PRODUCT']
        );
    }

    public function testAvailabilities()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $this->assertNull($dto->getAvailabilities());

        $dto->setAvailability('DE', active: true, price: 1337.0);
        $dto->setAvailability('de_CH', active: false, instock: 0);
        $dto->addAvailability('de', 'instock', 3);

        $this->assertSame(['active' => true, 'price' => 1337.0, 'instock' => 3], $dto->getAvailability('de'));
        $this->assertSame(['active' => false, 'instock' => 0], $dto->getAvailability('DE-CH'));
        $this->assertNull($dto->getAvailability('at'));
    }

    public function testAvailabilityOfUnknownFieldThrows()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Field "name" is not scoped by availability');

        (new ProductBaseDto('1', 'PRODUCT'))->addAvailability('de', 'name', 'Name');
    }

    public function testJsonSerializeIncludesAvailabilitiesAfterTranslations()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->setPrice(999.0);
        $dto->setActive(true);
        $dto->addTranslation('en', 'name', 'Phone');
        $dto->setAvailability('de', price: 1337.0);

        $this->assertSame([
            'id' => 'PRODUCT-1',
            'type' => 'PRODUCT',
            '_PRODUCT_i18n' => ['en' => ['name' => 'Phone']],
            '_PRODUCT_availability' => ['de' => ['price' => 1337.0]],
            '_PRODUCT' => ['price' => 999.0, 'active' => true],
        ], $dto->jsonSerialize());

        $withoutTranslations = (new ProductBaseDto('2', 'PRODUCT'))->setAvailability('ch', active: false);
        $this->assertSame(['id', 'type', '_PRODUCT_availability', '_PRODUCT'], array_keys($withoutTranslations->jsonSerialize()));
    }

    public function testJsonSerialize()
    {
        $id = '1';
        $type = 'PRODUCT';
        $dto = new ProductBaseDto($id, $type);
        $dto->setName('Name');
        $dto->setPrice(1.5);

        $cat = $this->createMock(CategoryDto::class);
        $cat->method('jsonSerialize')->willReturn(['A']);
        $dto->addCategory($cat);

        $expected = [
            'id' => $type . '-' . $id,
            'type' => $type,
            '_' . $type => [
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
        $dto->setName('Name');
        $dto->setPrice(1.5);

        $this->assertSame($dto, $dto->exportNullValues());

        $expected = [
            'id' => $type . '-' . $id,
            'type' => $type,
            '_' . $type => [
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
                'active' => null,
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
        $dto->setName('Name');

        $this->assertSame(['name' => 'Name'], $dto->jsonSerialize()['_PRODUCT']);
    }

    public function testExportNullValuesCanBeDisabledAgain()
    {
        $dto = new ProductBaseDto('1', 'PRODUCT');
        $dto->exportNullValues();
        $dto->exportNullValues(false);

        $this->assertSame([], $dto->jsonSerialize()['_PRODUCT']);
    }

    public function testSettersAreFluent()
    {
        $dto = (new ProductBaseDto('1'))
            ->setName('Phone')
            ->setPrice(99.0)
            ->addCategory((new CategoryDto())->addCategoryNode('Electronics'))
            ->setProperties((new ProductPropertyDto())->addProperty('Color', 'Black'))
            ->addTranslation('en', 'name', 'Phone')
            ->setAvailability('ch', active: false);

        $this->assertSame('Phone', $dto->getName());
        $this->assertSame(['Electronics'], $dto->getCategories());
        $this->assertSame(['active' => false], $dto->getAvailability('ch'));
    }

    public function testIdIsOnlyExportedInRoot()
    {
        $dto = new ProductBaseDto('1');
        $dto->setName('Name');

        $this->assertSame(
            ['id' => 'PRODUCT-1', 'type' => 'PRODUCT', '_PRODUCT' => ['name' => 'Name']],
            $dto->jsonSerialize()
        );
    }
}
