<?php

declare(strict_types=1);

namespace Shop\Extension;

use BatteryIncludedSdk\Shop\Extension\AbstractExtension;
use BatteryIncludedSdk\Shop\Extension\PromotionsExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PromotionsExtension::class)]
#[CoversClass(AbstractExtension::class)]
class PromotionsExtensionTest extends TestCase
{
    public function testExtensionInitialization()
    {
        $extension = new PromotionsExtension(['code1', 'code2']);
        $this->assertSame('promotions', $extension->getType());
        $this->assertSame(['code1', 'code2'], $extension->getData());
    }
}
