<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\SimilarSearch;

use BatteryIncludedSdk\SimilarSearch\SimilarSearchDto;
use BatteryIncludedSdk\SimilarSearch\SimilarSearchResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SimilarSearchResponse::class)]
#[CoversClass(SimilarSearchDto::class)]
class SimilarSearchResponseTest extends TestCase
{
    public function testSimilarSearchesAreParsedCorrectly(): void
    {
        $responseRaw = json_encode([
            'searches' => [
                [
                    'q' => 'test1',
                    'count' => 5,
                    'hits' => 10,
                    'highlighted' => 'highlight1',
                ],
                [
                    'q' => 'test2',
                    'count' => 3,
                    'hits' => 7,
                    'highlighted' => 'highlight2',
                ],
            ],
        ]);

        $response = new SimilarSearchResponse($responseRaw);

        $similarSearches = $response->getSimilarSearches();

        $this->assertCount(2, $similarSearches);
        $this->assertInstanceOf(SimilarSearchDto::class, $similarSearches[0]);
        $this->assertSame('test1', $similarSearches[0]->getQuery());
        $this->assertSame(5, $similarSearches[0]->getCount());
        $this->assertSame(10, $similarSearches[0]->getHits());
        $this->assertSame('highlight1', $similarSearches[0]->getHighlighted());
    }
}
