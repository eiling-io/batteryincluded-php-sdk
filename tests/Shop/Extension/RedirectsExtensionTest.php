<?php

declare(strict_types=1);

namespace Shop\Extension;

use BatteryIncludedSdk\Shop\Extension\AbstractExtension;
use BatteryIncludedSdk\Shop\Extension\RedirectsExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RedirectsExtension::class)]
#[CoversClass(AbstractExtension::class)]
class RedirectsExtensionTest extends TestCase
{
    public function testExtensionInitialization()
    {
        $extension = new RedirectsExtension(['code1', 'code2']);
        $this->assertSame('redirects', $extension->getType());
        $this->assertSame(['code1', 'code2'], $extension->getData());
    }
}
