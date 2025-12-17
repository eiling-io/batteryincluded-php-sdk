<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Presets;

use BatteryIncludedSdk\Presets\PresetsDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PresetsDto::class)]
class PresetsDtoTest extends TestCase
{
    public function testGettersReturnConstructorValues(): void
    {
        $dto = new PresetsDto('test-id', 'test-name');

        $this->assertSame('test-id', $dto->getId());
        $this->assertSame('test-name', $dto->getName());
    }
}
