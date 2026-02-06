<?php

declare(strict_types=1);

namespace Suggest;

use BatteryIncludedSdk\Suggest\SuggestSearchStruct;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SuggestSearchStruct::class)]
class SuggestSearchStructTest extends TestCase
{
    public function testSetAndGetQuery(): void
    {
        $struct = new SuggestSearchStruct();
        $struct->setQuery('test-query');
        $this->assertSame('test-query', $struct->getQuery());
    }

    public function testAddFilter(): void
    {
        $struct = new SuggestSearchStruct();
        $struct->addFilter('color', 'red');
        $struct->addFilter('color', 'blue');
        $this->assertSame(['color' => ['red', 'blue']], $struct->getFilters());
    }

    public function testAddFilters(): void
    {
        $struct = new SuggestSearchStruct();
        $filters = [
            'size' => [0 => 'M', 1 => 'L'],
            'brand' => [0 => 'Nike'],
        ];
        $struct->addFilters($filters);
        $this->assertSame($filters, $struct->getFilters());
    }

    public function testGetFiltersInitiallyEmpty(): void
    {
        $struct = new SuggestSearchStruct();
        $this->assertSame([], $struct->getFilters());
    }
}
