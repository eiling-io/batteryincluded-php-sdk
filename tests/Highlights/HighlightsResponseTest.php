<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Highlights;

use BatteryIncludedSdk\Highlights\HighlightsResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HighlightsResponse::class)]
class HighlightsResponseTest extends TestCase
{
    public function testGetHighlightsResponse(): void
    {
        $data = [
            'searches' => [
                ['q' => 'test1', 'count' => 5],
                ['q' => 'test2', 'count' => 3],
            ],
            'querySuggestions' => [
                ['suggestion' => 'foo'],
                ['suggestion' => 'bar'],
            ],
        ];
        $json = json_encode($data);

        $response = new HighlightsResponse($json);

        $this->assertSame($data['searches'], $response->getSearches());
        $this->assertSame($data['querySuggestions'], $response->getQuerySuggestions());
        $this->assertSame($data, $response->getAll());
    }
}
