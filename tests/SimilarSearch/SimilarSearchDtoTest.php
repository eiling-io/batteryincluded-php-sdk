<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\SimilarSearch;

use BatteryIncludedSdk\SimilarSearch\SimilarSearchDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SimilarSearchDto::class)]
class SimilarSearchDtoTest extends TestCase
{
    public function testGettersReturnConstructorValues(): void
    {
        $dto = new SimilarSearchDto('test-query', 5, 10, 'highlighted-text');

        $this->assertSame('test-query', $dto->getQuery());
        $this->assertSame(5, $dto->getCount());
        $this->assertSame(10, $dto->getHits());
        $this->assertSame('highlighted-text', $dto->getHighlighted());
    }
}
