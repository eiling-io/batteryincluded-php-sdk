<?php

declare(strict_types=1);

namespace Shop\Extension;

use BatteryIncludedSdk\Shop\Extension\AbstractExtension;
use BatteryIncludedSdk\Shop\Extension\TeaserExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TeaserExtension::class)]
#[CoversClass(AbstractExtension::class)]
class TeaserExtensionTest extends TestCase
{
    public function testExtensionInitialization()
    {
        $extension = new TeaserExtension(['code1', 'code2']);
        $this->assertSame('teaser', $extension->getType());
        $this->assertSame(['code1', 'code2'], $extension->getData());
    }
}
