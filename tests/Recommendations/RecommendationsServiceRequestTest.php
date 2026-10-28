<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Recommendations;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\HttpClientInterface;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Recommendations\RecommendationsResponse;
use BatteryIncludedSdk\Recommendations\RecommendationsService;
use BatteryIncludedSdk\Service\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecommendationsService::class)]
#[CoversClass(RecommendationsResponse::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(I18nResolver::class)]
#[UsesClass(Response::class)]
class RecommendationsServiceRequestTest extends TestCase
{
    private array $requests = [];

    public function testWithoutLocaleNoVariableIsSentAndTheDocumentsAreUntouched()
    {
        $response = $this->createService()->recommendByIdentifier('1002');

        $this->assertSame('https://api.test/collections/customer.test/documents/recommendations?id=PRODUCT-1002', $this->requests[0]['url']);
        $this->assertNull($response->getLocale());
        $this->assertArrayHasKey('_PRODUCT_i18n', $response->getRecommendations()['related'][0]['document']);
    }

    public function testTheLocaleIsSentNormalizedAndItsTranslationsAreMerged()
    {
        $response = $this->createService()->recommendByIdentifier('1002', 'PRODUCT', 'de_DE');

        $this->assertSame('https://api.test/collections/customer.test/documents/recommendations?id=PRODUCT-1002&v%5Blocale%5D=de-de', $this->requests[0]['url']);
        $this->assertSame('de_DE', $response->getLocale());
        $document = $response->getRecommendations()['related'][0]['document'];
        $this->assertSame(['name' => 'Fernseher', 'price' => 699], $document['_PRODUCT']);
        $this->assertArrayNotHasKey('_PRODUCT_i18n', $document);
    }

    private function createService(): RecommendationsService
    {
        // records the requests instead of sending them, the gateway may deliver every locale
        $httpClient = new class(function (array $request) {
            $this->requests[] = $request;
        }) implements HttpClientInterface {
            public function __construct(private \Closure $record)
            {
            }

            public function send(string $url, string $method, array $header, string $data): Response
            {
                ($this->record)(['url' => $url, 'method' => $method]);

                return new Response((string) json_encode([[
                    'type' => 'related',
                    'document' => [
                        'id' => 'PRODUCT-1003',
                        '_PRODUCT' => ['name' => 'TV', 'price' => 699],
                        '_PRODUCT_i18n' => ['de-de' => ['name' => 'Fernseher'], 'en-us' => ['name' => 'TV']],
                    ],
                ]]), 200);
            }
        };

        return new RecommendationsService(new ApiClient($httpClient, 'https://api.test/collections/', 'customer.test', 'secret'));
    }
}
