<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Client;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
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
        $this->expectException(\Exception::class);
        $apiClient->postJson('/clear', 'INVALID_ARGUMENT');
    }
}
