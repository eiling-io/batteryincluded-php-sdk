<?php

declare(strict_types=1);

namespace Service;

use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Service\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CurlHttpClient::class)]
#[CoversClass(Response::class)]
class CurlHttpClientTest extends TestCase
{
    public function testSend()
    {
        $httpClient = new CurlHttpClient();
        $this->expectException(\InvalidArgumentException::class);
        $httpClient->send('/clear', 'INVALID_ARGUMENT', [], '{}');
    }
}
