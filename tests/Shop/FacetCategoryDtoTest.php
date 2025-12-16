<?php

declare(strict_types=1);

namespace Shop;

use BatteryIncludedSdk\Shop\FacetCategoryDto;
use BatteryIncludedSdk\Shop\FacetDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FacetDto::class)]
#[CoversClass(FacetCategoryDto::class)]
class FacetCategoryDtoTest extends TestCase
{
    public function testBuildCategoryTreeSetsCheckedAndBuildsTree(): void
    {
        $data = [
            'field_name' => 'category',
            'counts' => [
                ['value' => 'A', 'count' => 5],
                ['value' => 'A > B', 'count' => 3],
                ['value' => 'A > B > C', 'count' => 1],
                ['value' => 'D', 'count' => 2],
            ],
        ];
        $appliedFilterValues = [
            'category' => ['A > B'],
        ];

        $dto = new FacetCategoryDto($data, $appliedFilterValues);
        $categories = $dto->getCategories();

        $this->assertArrayHasKey('A', $categories);
        $this->assertArrayHasKey('B', $categories['A']['childs']);
        $this->assertTrue($categories['A']['checked'] ?? false);
        $this->assertTrue($categories['A']['childs']['B']['checked'] ?? false);
        $this->assertFalse($categories['A']['childs']['B']['childs']['C']['checked'] ?? true);
        $this->assertSame(3, $categories['A']['childs']['B']['count']);
        $this->assertSame(5, $categories['A']['count']);
    }
}
