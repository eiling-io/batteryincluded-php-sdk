<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Service;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\CartRecommendations\CartRecommendationsResponse;
use BatteryIncludedSdk\Highlights\HighlightsResponse;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Presets\PresetsDto;
use BatteryIncludedSdk\Presets\PresetsResponse;
use BatteryIncludedSdk\Recommendations\RecommendationsResponse;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Shop\BrowseResponse;
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\SimilarSearch\SimilarSearchResponse;
use BatteryIncludedSdk\Suggest\SuggestResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * An error of the api must not break the shop: every response renders empty and reports the error.
 */
#[CoversClass(Response::class)]
#[CoversClass(SuggestResponse::class)]
#[CoversClass(RecommendationsResponse::class)]
#[CoversClass(CartRecommendationsResponse::class)]
#[CoversClass(PresetsResponse::class)]
#[CoversClass(HighlightsResponse::class)]
#[CoversClass(SimilarSearchResponse::class)]
#[CoversClass(BrowseResponse::class)]
#[UsesClass(BrowseSearchStruct::class)]
#[UsesClass(PresetsDto::class)]
#[UsesClass(I18nResolver::class)]
#[UsesClass(AvailabilityResolver::class)]
class ErrorResponseTest extends TestCase
{
    public static function errorBodies(): array
    {
        return [
            'json message' => ['{"message":"Internal Server Error"}', 500],
            'html' => ['<html>Bad Gateway</html>', 502],
            'empty' => ['', 503],
            'not found' => ['{"message":"Not Found"}', 404],
        ];
    }

    #[DataProvider('errorBodies')]
    public function testErrorResponsesRenderEmpty(string $body, int $statusCode)
    {
        $suggest = new SuggestResponse($body, $statusCode, 'de');
        $this->assertSame([], $suggest->getDocuments());
        $this->assertSame([], $suggest->getResolvedDocuments());
        $this->assertSame([], $suggest->getQueryCompletions());
        $this->assertSame(0, $suggest->getFounds());

        $empty = ['related' => [], 'also' => [], 'together' => []];
        $this->assertSame($empty, (new RecommendationsResponse($body, $statusCode, 'de', 'ch'))->getRecommendations());
        $this->assertSame($empty, (new CartRecommendationsResponse($body, $statusCode))->getRecommendations());
        $this->assertSame([], (new PresetsResponse($body, $statusCode))->getPresets());
        $this->assertSame(['searches' => [], 'querySuggestions' => []], (new HighlightsResponse($body, $statusCode))->getAll());
        $this->assertSame([], (new SimilarSearchResponse($body, $statusCode))->getSimilarSearches());

        $browse = new BrowseResponse($body, new BrowseSearchStruct(), $statusCode);
        $this->assertFalse($browse->isSuccessful());
        $this->assertSame([], $browse->getHits());
        $this->assertSame([], $browse->getFacets());
        $this->assertSame([], $browse->getAllExtensions());
        $this->assertSame(0, $browse->getFound());
        $this->assertSame(0, $browse->getPages());
    }

    public function testErrorMessage()
    {
        $this->assertSame('Internal Server Error', (new Response('{"message":"Internal Server Error"}', 500))->getErrorMessage());
        $this->assertSame('<html>Bad Gateway</html>', (new Response('<html>Bad Gateway</html>', 502))->getErrorMessage());
        $this->assertNull((new Response('{"message":"ok"}', 200))->getErrorMessage());
    }

    public function testSuccessfulResponse()
    {
        $this->assertTrue((new Response('[]', 200))->isSuccessful());
        $this->assertTrue((new Response('', 204))->isSuccessful());
        $this->assertFalse((new Response('', 301))->isSuccessful());
        // a json scalar is no body the responses can work with
        $this->assertSame([], (new Response('"text"', 200))->getBody());
    }

    public function testUnexpectedEntriesOfASuccessfulResponseAreSkipped()
    {
        $suggest = new SuggestResponse('[{"kind":"document","found":1},"text",{"hits":[]},{"kind":"unknown"}]', 200);
        $this->assertSame([], $suggest->getDocuments());
        $this->assertSame([], $suggest->getFacets());
        $this->assertSame(1, $suggest->getFounds());

        $recommendations = new RecommendationsResponse('[{"type":"also","document":{}},{"document":{}},"text"]', 200);
        $this->assertSame(['related' => [], 'also' => [['type' => 'also', 'document' => []]], 'together' => []], $recommendations->getRecommendations());

        $presets = new PresetsResponse('[{"id":"1","name":"Sale"},{"id":"2"}]', 200);
        $this->assertCount(1, $presets->getPresets());

        $browse = new BrowseResponse('{"found":1,"extensions":[{"type":"teaser"},"text"],"facet_counts":["text"]}', new BrowseSearchStruct());
        $this->assertSame([], $browse->getAllExtensions());
        $this->assertSame([], $browse->getFacets());
    }
}
