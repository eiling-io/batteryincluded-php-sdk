<?php

declare(strict_types=1);

namespace Suggest;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Suggest\CompletionDto;
use BatteryIncludedSdk\Suggest\SuggestResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[UsesClass(CompletionDto::class)]
#[CoversClass(SuggestResponse::class)]
#[UsesClass(Response::class)]
#[UsesClass(I18nResolver::class)]
#[UsesClass(AvailabilityResolver::class)]
class SuggestResponseTest extends TestCase
{
    public function testSuggestResponseInitialization()
    {
        $responseRaw = (string) json_encode([
            [
                'kind' => 'document',
                'found' => 2,
                'llm' => true,
                'hits' => [
                    ['highlighted' => '<b>Dokument 1</b>'],
                    ['highlighted' => '<b>Dokument 2</b>'],
                ],
            ],
            [
                'kind' => 'query-completion',
                'hits' => [
                    [
                        'value' => 'Vorschlag 1',
                        'highlighted' => '<b>Vorschlag 1</b>',
                        'score' => 0.9,
                        'source' => 'testsource',
                    ],
                    [
                        'value' => 'Vorschlag 2',
                        'highlighted' => '<b>Vorschlag 2</b>',
                        'score' => 0.8,
                        'source' => 'testsource',
                    ],
                ],
            ],
            [
                'kind' => 'facet._PRODUCT.properties.Gerät',
                'found' => 1,
                'field_name' => '_PRODUCT.properties.Gerät',
                'hits' => [
                    [
                        'count' => 240,
                        'highlighted' => '<mark>Mac</mark>Book',
                        'value' => 'MacBook',
                    ],
                ],
            ],
        ]);

        $suggestResponse = new SuggestResponse($responseRaw, 200);

        $this->assertEquals(2, $suggestResponse->getFounds());
        $this->assertEquals(['<b>Dokument 1</b>', '<b>Dokument 2</b>'], $suggestResponse->getDocuments());

        $completions = $suggestResponse->getQueryCompletions();
        $this->assertCount(2, $completions);
        $this->assertInstanceOf(CompletionDto::class, $completions[0]);
        $this->assertEquals('Vorschlag 1', $completions[0]->getValue());
        $this->assertEquals('<b>Vorschlag 1</b>', $completions[0]->getHighlighted());
        $this->assertEquals(0.9, $completions[0]->getScore());
        $this->assertEquals('testsource', $completions[0]->getSource());

        $this->assertCount(1, $suggestResponse->getFacets());
        $this->assertEquals('facet._PRODUCT.properties.Gerät', $suggestResponse->getFacets()[0]['kind']);
        $this->assertTrue($suggestResponse->isLLM());
    }

    public function testResolvedDocumentsMergeTranslationsAndAvailability()
    {
        // the gateway already resolved the nodes for the requested locale and scope
        $responseRaw = (string) json_encode([[
            'kind' => 'document',
            'found' => 1,
            'hits' => [['highlighted' => [
                'id' => 'PRODUCT-1',
                '_PRODUCT' => ['name' => 'Fernseher', 'price' => 699],
                '_PRODUCT_i18n' => ['name' => '<mark>TV</mark>'],
                '_PRODUCT_availability' => ['price' => 749],
            ]]],
        ]]);

        $response = new SuggestResponse($responseRaw, 200, 'en', 'ch');

        $this->assertSame(
            [['id' => 'PRODUCT-1', '_PRODUCT' => ['name' => '<mark>TV</mark>', 'price' => 749]]],
            $response->getResolvedDocuments()
        );
        $this->assertArrayHasKey('_PRODUCT_i18n', $response->getDocuments()[0]);
        $this->assertSame('en', $response->getLocale());
        $this->assertSame('ch', $response->getAvailability());
    }
}
