<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Highlights;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\HttpClientInterface;
use BatteryIncludedSdk\Highlights\HighlightsResponse;
use BatteryIncludedSdk\Highlights\HighlightsService;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Service\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HighlightsService::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(HighlightsResponse::class)]
#[UsesClass(I18nResolver::class)]
#[UsesClass(Response::class)]
class HighlightsServiceRequestTest extends TestCase
{
    private array $requests = [];

    public function testWithoutLocaleNoVariableIsSent()
    {
        $this->createHighlightsService()->getHighlights();

        $this->assertSame('https://api.test/collections/customer.test/documents/highlights', $this->requests[0]['url']);
    }

    public function testTheLocaleIsSentNormalized()
    {
        $highlights = $this->createHighlightsService()->getHighlights('de_DE');

        $this->assertSame('https://api.test/collections/customer.test/documents/highlights?v%5Blocale%5D=de-de', $this->requests[0]['url']);
        $this->assertSame(['Jeans in Größe XL'], $highlights->getQuerySuggestions());
    }

    private function createHighlightsService(): HighlightsService
    {
        // records the requests instead of sending them
        $httpClient = new class(function (array $request) {
            $this->requests[] = $request;
        }) implements HttpClientInterface {
            public function __construct(private \Closure $record)
            {
            }

            public function send(string $url, string $method, array $header, string $data): Response
            {
                ($this->record)(['url' => $url, 'method' => $method]);

                return new Response('{"searches":[],"querySuggestions":["Jeans in Größe XL"]}', 200);
            }
        };

        return new HighlightsService(new ApiClient($httpClient, 'https://api.test/collections/', 'customer.test', 'secret'));
    }
}
