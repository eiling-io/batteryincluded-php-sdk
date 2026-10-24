<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Availability;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AvailabilityResolver::class)]
class AvailabilityResolverTest extends TestCase
{
    private const DOCUMENT = [
        'id' => 'PRODUCT-1',
        'type' => 'PRODUCT',
        '_PRODUCT_availability' => [
            'de' => ['active' => true, 'price' => 1337.0],
            'ch' => ['active' => false, 'instock' => 0],
            'b2b-de' => ['price' => 1100.0],
        ],
        '_PRODUCT' => ['name' => 'Smartphone', 'price' => 999.0, 'active' => true, 'instock' => 5],
    ];

    public function testGetKey()
    {
        $this->assertSame('_PRODUCT_availability', AvailabilityResolver::getKey('product'));
        $this->assertSame('availability', AvailabilityResolver::VARIABLE);
    }

    public function testScopeOverwritesPayload()
    {
        $this->assertSame(
            ['name' => 'Smartphone', 'price' => 1337.0, 'active' => true, 'instock' => 5],
            AvailabilityResolver::resolveDocument(self::DOCUMENT, 'DE')['_PRODUCT']
        );
    }

    public function testFalseAndZeroOverwritePayload()
    {
        $this->assertSame(
            ['name' => 'Smartphone', 'price' => 999.0, 'active' => false, 'instock' => 0],
            AvailabilityResolver::resolveDocument(self::DOCUMENT, 'ch')['_PRODUCT']
        );
    }

    public function testSalesChannelScopes()
    {
        $this->assertSame(1100.0, AvailabilityResolver::resolveDocument(self::DOCUMENT, 'B2B_DE')['_PRODUCT']['price']);
    }

    public function testMissingScopeKeepsPayloadWithoutFallback()
    {
        // unlike the locales there is no fallback from `de-at` to `de`
        $expected = ['id' => 'PRODUCT-1', 'type' => 'PRODUCT', '_PRODUCT' => self::DOCUMENT['_PRODUCT']];

        $this->assertSame($expected, AvailabilityResolver::resolveDocument(self::DOCUMENT, 'de-at'));
        $this->assertSame($expected, AvailabilityResolver::resolveDocument(self::DOCUMENT, ''));
    }

    public function testNodeResolvedByTheGatewayIsMerged()
    {
        $document = ['_PRODUCT_availability' => ['price' => 1337.0, 'active' => false], '_PRODUCT' => ['price' => 999.0, 'active' => true]];

        $this->assertSame(['_PRODUCT' => ['price' => 1337.0, 'active' => false]], AvailabilityResolver::resolveDocument($document, ''));
    }

    public function testTranslationNodesAreLeftUntouched()
    {
        $document = ['_PRODUCT_i18n' => ['en' => ['name' => 'Phone']], '_PRODUCT' => ['name' => 'Handy']];

        $this->assertSame($document, AvailabilityResolver::resolveDocument($document, 'en'));
    }

    public function testResolveHits()
    {
        $hits = [['document' => self::DOCUMENT, 'highlight' => []]];

        $this->assertSame(1337.0, AvailabilityResolver::resolveHits($hits, 'de')[0]['document']['_PRODUCT']['price']);
    }
}
