<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Service;

use BatteryIncludedSdk\Service\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
    public function testGetBodyReturnsDecodedArray()
    {
        $data = ['foo' => 'bar', 'baz' => 123];
        $json = json_encode($data);
        $response = new Response($json);

        $this->assertSame($data, $response->getBody());
    }

    public function testGetRawResponseReturnsOriginalString()
    {
        $json = '{"key":"value"}';
        $response = new Response($json);

        $this->assertSame($json, $response->getRawResponse());
    }
}
