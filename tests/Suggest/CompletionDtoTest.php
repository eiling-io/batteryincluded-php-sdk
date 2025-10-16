<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Suggest;

use BatteryIncludedSdk\Suggest\CompletionDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CompletionDto::class)]
class CompletionDtoTest extends TestCase
{
    public function testInitializationAndGetters()
    {
        $dto = new CompletionDto(
            'Testwert',
            '<b>Testwert</b>',
            0.95,
            'testsource'
        );

        $this->assertEquals('Testwert', $dto->getValue());
        $this->assertEquals('<b>Testwert</b>', $dto->getHighlighted());
        $this->assertEquals(0.95, $dto->getScore());
        $this->assertEquals('testsource', $dto->getSource());
    }
}
