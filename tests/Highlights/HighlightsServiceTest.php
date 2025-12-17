<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Highlights;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Highlights\HighlightsResponse;
use BatteryIncludedSdk\Highlights\HighlightsService;
use BatteryIncludedSdk\Service\AbstractService;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdkTests\Helper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HighlightsResponse::class)]
#[CoversClass(HighlightsService::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(CurlHttpClient::class)]
#[UsesClass(Response::class)]
#[UsesClass(AbstractService::class)]
class HighlightsServiceTest extends TestCase
{
    public function testHighlightsMethodAgainstLiveApi()
    {
        $HighlightsService = new HighlightsService(Helper::getApiClient());
        $result = $HighlightsService->getHighlights();

        $this->assertInstanceOf(HighlightsResponse::class, $result);
    }
}
