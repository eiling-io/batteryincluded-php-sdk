<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Client;

use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Exception\NetworkException;
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

    public function testUnreachableHostThrowsNetworkException()
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('GET http://127.0.0.1:1/ failed');

        (new CurlHttpClient(readTimeout: 1.0, connectTimeout: 1.0))->send('http://127.0.0.1:1/', 'GET', [], '');
    }

    public function testTimeoutThrowsNetworkException()
    {
        // a non-routable address never answers, the connect timeout has to end the request
        $start = microtime(true);
        try {
            (new CurlHttpClient(readTimeout: 0.3, connectTimeout: 0.2))->send('http://10.255.255.1/', 'GET', [], '');
            $this->fail('NetworkException expected');
        } catch (NetworkException) {
            $this->assertLessThan(2.0, microtime(true) - $start);
        }
    }

    public function testWriteTimeoutAppliesToImports()
    {
        $start = microtime(true);
        try {
            (new CurlHttpClient(readTimeout: 60.0, connectTimeout: 0.0, writeTimeout: 0.3))->send('http://10.255.255.1/', 'POST', [], '{}');
            $this->fail('NetworkException expected');
        } catch (NetworkException) {
            $this->assertLessThan(2.0, microtime(true) - $start);
        }
    }
}
