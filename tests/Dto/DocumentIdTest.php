<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Dto;

use BatteryIncludedSdk\Dto\AbstractAvailabilityDto;
use BatteryIncludedSdk\Dto\AbstractDto;
use BatteryIncludedSdk\Dto\AbstractTranslatableDto;
use BatteryIncludedSdk\Dto\BlogBaseDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\I18n\I18nResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * The root id `<TYPE>-<identifier>` identifies a document for imports, partial updates and deletes.
 */
#[CoversClass(AbstractDto::class)]
#[UsesClass(AbstractTranslatableDto::class)]
#[UsesClass(AbstractAvailabilityDto::class)]
#[UsesClass(ProductBaseDto::class)]
#[UsesClass(BlogBaseDto::class)]
#[UsesClass(I18nResolver::class)]
class DocumentIdTest extends TestCase
{
    public function testSameShopIdOfDifferentTypesDoesNotCollide()
    {
        $product = new ProductBaseDto('1');
        $blog = new BlogBaseDto('1');
        $faq = new BlogBaseDto('1', 'FAQ');

        $this->assertSame('PRODUCT-1', $product->jsonSerialize()['id']);
        $this->assertSame('BLOG-1', $blog->jsonSerialize()['id']);
        $this->assertSame('FAQ-1', $faq->jsonSerialize()['id']);
    }

    public function testExtendedDtoKeepsRootIdAndMergesCustomFields()
    {
        // the extension pattern of docs/syncing.md
        $dto = new class('1002') extends ProductBaseDto {
            private ?string $material = null;

            public function setMaterial(?string $material): void
            {
                $this->material = $material;
            }

            public function jsonSerialize(): array
            {
                return array_merge_recursive(
                    parent::jsonSerialize(),
                    ['_' . $this->getType() => $this->filterJsonValues(['material' => $this->material])]
                );
            }
        };
        $dto->setName('TV');
        $dto->setMaterial('Aluminium');
        $dto->addTranslation('en', 'name', 'Television');

        $this->assertSame(
            [
                'id' => 'PRODUCT-1002',
                'type' => 'PRODUCT',
                '_PRODUCT_i18n' => ['en' => ['name' => 'Television']],
                '_PRODUCT' => ['name' => 'TV', 'material' => 'Aluminium'],
            ],
            $dto->jsonSerialize()
        );
    }

    public function testExtendedDtoWithoutStandardFields()
    {
        $dto = new class('1') extends ProductBaseDto {
            public function jsonSerialize(): array
            {
                return array_merge_recursive(
                    parent::jsonSerialize(),
                    ['_' . $this->getType() => $this->filterJsonValues(['material' => 'Glass'])]
                );
            }
        };

        $this->assertSame(['id' => 'PRODUCT-1', 'type' => 'PRODUCT', '_PRODUCT' => ['material' => 'Glass']], $dto->jsonSerialize());
    }
}
