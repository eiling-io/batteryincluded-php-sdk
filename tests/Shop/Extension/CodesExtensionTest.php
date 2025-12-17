<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Shop\Extension;

use BatteryIncludedSdk\Shop\Extension\AbstractExtension;
use BatteryIncludedSdk\Shop\Extension\CodesExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CodesExtension::class)]
#[CoversClass(AbstractExtension::class)]
class CodesExtensionTest extends TestCase
{
    public function testExtensionInitialization()
    {
        $extension = new CodesExtension(['code1', 'code2']);
        $this->assertSame('codes', $extension->getType());
        $this->assertSame(['code1', 'code2'], $extension->getData());
    }
}
