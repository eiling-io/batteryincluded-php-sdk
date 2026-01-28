<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Presets;

use BatteryIncludedSdk\Presets\PresetsDto;
use BatteryIncludedSdk\Presets\PresetsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PresetsResponse::class)]
#[CoversClass(PresetsDto::class)]
class PresetsResponseTest extends TestCase
{
    public function testGetPresetsReturnsPresetsDtoArray(): void
    {
        $data = [
            ['id' => '1', 'name' => 'Preset 1'],
            ['id' => '2', 'name' => 'Preset 2'],
        ];
        $json = json_encode($data);

        $response = new PresetsResponse($json, 200);
        $presets = $response->getPresets();

        $this->assertCount(2, $presets);
        $this->assertContainsOnlyInstancesOf(PresetsDto::class, $presets);

        $this->assertSame('1', $presets[0]->getId());
        $this->assertSame('Preset 1', $presets[0]->getName());
        $this->assertSame('2', $presets[1]->getId());
        $this->assertSame('Preset 2', $presets[1]->getName());
    }
}
