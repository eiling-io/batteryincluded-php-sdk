<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\I18n;

use BatteryIncludedSdk\I18n\I18nResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(I18nResolver::class)]
class I18nResolverTest extends TestCase
{
    public function testNormalizeLocale()
    {
        $this->assertSame('de', I18nResolver::normalizeLocale(' DE '));
        $this->assertSame('de-ch', I18nResolver::normalizeLocale('de_CH'));
    }

    public function testGetKey()
    {
        $this->assertSame('_PRODUCT_i18n', I18nResolver::getKey('product'));
        $this->assertSame('_BLOG_i18n', I18nResolver::getKey('BLOG'));
    }

    public function testTranslationsResolvedByTheGatewayAreMerged()
    {
        $document = [
            'id' => 'PRODUCT-1002',
            'type' => 'PRODUCT',
            '_PRODUCT_i18n' => [
                'name' => 'Ultra HD HDR LED TV 65"',
                'description' => null,
                'categories' => ['Electronics', 'Electronics > TVs'],
            ],
            '_PRODUCT' => [
                'id' => '1002',
                'description' => 'Beschreibung',
                'price' => 699,
            ],
        ];

        $this->assertSame([
            'id' => 'PRODUCT-1002',
            'type' => 'PRODUCT',
            '_PRODUCT' => [
                'id' => '1002',
                'description' => 'Beschreibung',
                'price' => 699,
                'name' => 'Ultra HD HDR LED TV 65"',
                'categories' => ['Electronics', 'Electronics > TVs'],
            ],
        ], I18nResolver::resolveDocument($document, 'en'));
    }

    public function testTranslationsScopedByLocaleAreResolved()
    {
        $document = [
            '_PRODUCT' => ['name' => 'Fernseher', 'price' => 699],
            '_PRODUCT_i18n' => [
                'de' => ['name' => 'Fernseher'],
                'en' => ['name' => 'TV', 'categories' => ['Electronics']],
            ],
        ];

        $this->assertSame(
            ['_PRODUCT' => ['name' => 'TV', 'price' => 699, 'categories' => ['Electronics']]],
            I18nResolver::resolveDocument($document, 'EN')
        );
    }

    public function testMissingLocaleKeepsFallbackAndRemovesNode()
    {
        $document = ['_PRODUCT' => ['name' => 'Fernseher'], '_PRODUCT_i18n' => ['en' => ['name' => 'TV']]];

        $this->assertSame(['_PRODUCT' => ['name' => 'Fernseher']], I18nResolver::resolveDocument($document, 'fr'));
        $this->assertSame(['_PRODUCT' => ['name' => 'Fernseher']], I18nResolver::resolveDocument($document, ''));
        $this->assertSame(['_PRODUCT' => ['name' => 'Fernseher']], I18nResolver::resolveDocument(['_PRODUCT' => ['name' => 'Fernseher'], '_PRODUCT_i18n' => null], 'en'));
    }

    public function testRegionalLocaleFallsBackToLanguage()
    {
        $document = ['_PRODUCT' => ['name' => 'Fahrrad'], '_PRODUCT_i18n' => ['DE' => ['name' => 'Velo'], 'en' => ['name' => 'Bike']]];

        $this->assertSame(['_PRODUCT' => ['name' => 'Velo']], I18nResolver::resolveDocument($document, 'de_CH'));
    }

    public function testNodeWithoutPayloadIsRemoved()
    {
        $document = ['_PRODUCT' => ['name' => 'Hose'], '_BLOG_i18n' => ['title' => 'News']];

        $this->assertSame(['_PRODUCT' => ['name' => 'Hose']], I18nResolver::resolveDocument($document, 'en'));
    }

    public function testDocumentWithoutTranslationsIsUntouched()
    {
        $this->assertSame(['_PRODUCT' => ['name' => 'iPhone']], I18nResolver::resolveDocument(['_PRODUCT' => ['name' => 'iPhone']], 'en'));
    }

    public function testResolveHitsHandlesDocumentsAndAllTypes()
    {
        $hits = [
            [
                'document' => [
                    'id' => 'PRODUCT-1',
                    'type' => 'PRODUCT',
                    '_PRODUCT' => ['name' => 'Hose'],
                    '_PRODUCT_i18n' => ['name' => 'Trousers'],
                ],
                'highlight' => [],
            ],
            [
                'id' => 'BLOG-1',
                'type' => 'BLOG',
                '_BLOG' => ['title' => 'Neuigkeiten'],
                '_BLOG_i18n' => ['en' => ['title' => 'News']],
            ],
            'invalid',
        ];

        $this->assertSame([
            [
                'document' => [
                    'id' => 'PRODUCT-1',
                    'type' => 'PRODUCT',
                    '_PRODUCT' => ['name' => 'Trousers'],
                ],
                'highlight' => [],
            ],
            [
                'id' => 'BLOG-1',
                'type' => 'BLOG',
                '_BLOG' => ['title' => 'News'],
            ],
            'invalid',
        ], I18nResolver::resolveHits($hits, 'en'));
    }
}
