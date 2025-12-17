<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Presets;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Presets\PresetsDto;
use BatteryIncludedSdk\Presets\PresetsResponse;
use BatteryIncludedSdk\Presets\PresetsService;
use BatteryIncludedSdk\Service\AbstractService;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdkTests\Helper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PresetsResponse::class)]
#[CoversClass(PresetsService::class)]
#[CoversClass(PresetsDto::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(CurlHttpClient::class)]
#[UsesClass(Response::class)]
#[UsesClass(AbstractService::class)]
class PresetsServiceTest extends TestCase
{
    public function testPresetsMethodAgainstLiveApi()
    {
        $PresetsService = new PresetsService(Helper::getApiClient());
        $result = $PresetsService->getPresets();

        $this->assertContainsOnlyInstancesOf(PresetsDto::class, $result->getPresets());

        $this->assertInstanceOf(PresetsResponse::class, $result);
    }
}
