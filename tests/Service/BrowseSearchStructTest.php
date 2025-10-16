<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Service;

use BatteryIncludedSdk\Service\BrowseSearchStruct;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(BrowseSearchStruct::class)]
class BrowseSearchStructTest extends TestCase
{
    public function testDefaultValues()
    {
        $struct = new BrowseSearchStruct();
        $this->assertSame('', $struct->getQuery());
        $this->assertSame([], $struct->getFilters());
        $this->assertSame(10, $struct->getPerPage());
        $this->assertSame(1, $struct->getPage());
        $this->assertSame('price:desc', $struct->getSort());
    }

    public function testSetAndGetQuery()
    {
        $struct = new BrowseSearchStruct();
        $struct->setQuery('test');
        $this->assertSame('test', $struct->getQuery());
    }

    public function testAddAndGetFilters()
    {
        $struct = new BrowseSearchStruct();
        $struct->addFilter('color', 'red');
        $struct->addFilter('color', 'blue');
        $struct->addFilter('size', 'L');
        $expected = [
            'color' => ['red', 'blue'],
            'size' => ['L'],
        ];
        $this->assertSame($expected, $struct->getFilters());
    }

    public function testAddFiltersAndGetFilters()
    {
        $struct = new BrowseSearchStruct();
        $struct->addFilters(
            [
                'color' => ['red', 'blue'],
                'size' => ['L'],
            ]
        );

        $expected = [
            'color' => ['red', 'blue'],
            'size' => ['L'],
        ];
        $this->assertSame($expected, $struct->getFilters());
    }

    public function testSetAndGetPerPage()
    {
        $struct = new BrowseSearchStruct();
        $struct->setPerPage(50);
        $this->assertSame(50, $struct->getPerPage());
    }

    public function testSetAndGetPage()
    {
        $struct = new BrowseSearchStruct();
        $struct->setPage(3);
        $this->assertSame(3, $struct->getPage());
    }

    public function testSetAndGetSort()
    {
        $struct = new BrowseSearchStruct();
        $struct->setSort('name:asc');
        $this->assertSame('name:asc', $struct->getSort());
    }
}
