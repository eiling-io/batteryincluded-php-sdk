<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Polyfill;

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

/**
 * Runs against the native mb_trim() on php >= 8.4 and against the polyfill of the sdk on older versions.
 */
#[CoversNothing]
class MbTrimTest extends TestCase
{
    public function testTrimsWhitespace()
    {
        $this->assertSame('de_CH', mb_trim(" \t\n de_CH \r\n"));
        $this->assertSame('Größe', mb_trim("\u{00A0}\u{3000}Größe\u{2009}"));
        $this->assertSame('a b', mb_trim('a b'));
        $this->assertSame('', mb_trim('   '));
    }

    public function testTrimsGivenCharacters()
    {
        $this->assertSame('Apple > iPhone', mb_trim('/.Apple > iPhone./', '/.'));
        $this->assertSame('ä', mb_trim('ööäöö', 'ö'));
    }
}
