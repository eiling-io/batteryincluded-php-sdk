<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\CartRecommendations;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\CartRecommendations\CartRecommendationsResponse;
use BatteryIncludedSdk\CartRecommendations\CartRecommendationsService;
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\HttpClientInterface;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Recommendations\RecommendationsResponse;
use BatteryIncludedSdk\Recommendations\RecommendationsService;
use BatteryIncludedSdk\Service\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CartRecommendationsService::class)]
#[CoversClass(CartRecommendationsResponse::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(I18nResolver::class)]
#[UsesClass(AvailabilityResolver::class)]
#[UsesClass(RecommendationsResponse::class)]
#[UsesClass(RecommendationsService::class)]
#[UsesClass(Response::class)]
class CartRecommendationsServiceRequestTest extends TestCase
{
    private array $requests = [];

    public function testWithoutLocaleNoVariableIsSent()
    {
        $response = $this->createService()->recommendByIdentifiers(['1002']);

        $this->assertSame('https://api.test/collections/customer.test/documents/recommendations/cart?ids%5B0%5D=PRODUCT-1002', $this->requests[0]['url']);
        $this->assertNull($response->getLocale());
    }

    public function testTheLocaleIsSentWithTheFiltersAndItsTranslationsAreMerged()
    {
        $response = $this->createService()->recommendByIdentifiers(['1002'], ['_PRODUCT_i18n.categories' => ['Fernseher']], 'PRODUCT', 'de_DE');

        $this->assertSame('ids[0]=PRODUCT-1002&f[_PRODUCT_i18n.categories][0]=Fernseher&v[locale]=de-de', urldecode((string) parse_url($this->requests[0]['url'], PHP_URL_QUERY)));
        $this->assertSame('Fernseher', $response->getRecommendations()['together'][0]['document']['_PRODUCT']['name']);
    }

    public function testTheAvailabilityIsSentAndMerged()
    {
        $response = $this->createService()->recommendByIdentifiers(['1002'], [], 'product', null, 'CH');

        $this->assertSame('ids[0]=PRODUCT-1002&v[availability]=ch', urldecode((string) parse_url($this->requests[0]['url'], PHP_URL_QUERY)));
        $this->assertFalse($response->getRecommendations()['together'][0]['document']['_PRODUCT']['active']);
    }

    private function createService(): CartRecommendationsService
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
                    'type' => 'together',
                    'document' => [
                        'id' => 'PRODUCT-1003',
                        '_PRODUCT' => ['name' => 'TV', 'active' => true],
                        '_PRODUCT_i18n' => ['de-de' => ['name' => 'Fernseher'], 'en-us' => ['name' => 'TV']],
                        '_PRODUCT_availability' => ['ch' => ['active' => false]],
                    ],
                ]]), 200);
            }
        };

        return new CartRecommendationsService(new ApiClient($httpClient, 'https://api.test/collections/', 'customer.test', 'secret'));
    }
}
