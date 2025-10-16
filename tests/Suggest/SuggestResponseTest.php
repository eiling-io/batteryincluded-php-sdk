<?php

declare(strict_types=1);

namespace Suggest;

use BatteryIncludedSdk\Suggest\CompletionDto;
use BatteryIncludedSdk\Suggest\SuggestResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[UsesClass(CompletionDto::class)]
#[CoversClass(SuggestResponse::class)]
class SuggestResponseTest extends TestCase
{
    public function testSuggestResponseInitialization()
    {
        $responseRaw = json_encode([
            [
                'kind' => 'document',
                'found' => 2,
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
        ]);

        $suggestResponse = new SuggestResponse($responseRaw);

        $this->assertEquals(2, $suggestResponse->getFounds());
        $this->assertEquals(['<b>Dokument 1</b>', '<b>Dokument 2</b>'], $suggestResponse->getDocuments());

        $completions = $suggestResponse->getQueryCompletions();
        $this->assertCount(2, $completions);
        $this->assertInstanceOf(CompletionDto::class, $completions[0]);
        $this->assertEquals('Vorschlag 1', $completions[0]->getValue());
        $this->assertEquals('<b>Vorschlag 1</b>', $completions[0]->getHighlighted());
        $this->assertEquals(0.9, $completions[0]->getScore());
        $this->assertEquals('testsource', $completions[0]->getSource());
    }
}
