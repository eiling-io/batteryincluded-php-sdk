<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Client;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Client\HttpClientInterface;
use BatteryIncludedSdk\Exception\NetworkException;
use BatteryIncludedSdk\Service\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiClient::class)]
#[CoversClass(CurlHttpClient::class)]
#[CoversClass(Response::class)]
class ApiClientTest extends TestCase
{
    public function testPostJson()
    {
        $apiClient = new ApiClient(new CurlHttpClient(), 'https://aaa', 'collection', 'apikey');
        $this->expectException(NetworkException::class);
        $apiClient->postJson('/clear', 'INVALID_ARGUMENT');
    }

    public function testEveryRequestSendsApiKeyAndUserAgent()
    {
        $httpClient = new class implements HttpClientInterface {
            public array $header = [];

            public function send(string $url, string $method, array $header, string $data): Response
            {
                $this->header = $header;

                return new Response('{}', 200);
            }
        };

        (new ApiClient($httpClient, 'https://api.test/collections/', 'customer.test', 'secret'))->getJson('/documents/presets');

        $this->assertContains('X-BI-API-KEY: secret', $httpClient->header);
        $this->assertContains('User-Agent: batteryincluded-php-sdk/' . ApiClient::getVersion() . ' php/' . PHP_VERSION, $httpClient->header);
        $this->assertNotSame('', ApiClient::getVersion());
    }
}
