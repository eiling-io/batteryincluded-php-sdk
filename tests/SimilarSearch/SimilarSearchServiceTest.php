<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\SimilarSearch;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Service\AbstractService;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\SimilarSearch\SimilarSearchDto;
use BatteryIncludedSdk\SimilarSearch\SimilarSearchResponse;
use BatteryIncludedSdk\SimilarSearch\SimilarSearchService;
use BatteryIncludedSdkTests\Helper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SimilarSearchResponse::class)]
#[CoversClass(SimilarSearchService::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(CurlHttpClient::class)]
#[UsesClass(Response::class)]
#[UsesClass(AbstractService::class)]
class SimilarSearchServiceTest extends TestCase
{
    public function testSimilarSearchMethodAgainstLiveApi()
    {
        $similarSearchService = new SimilarSearchService(Helper::getApiClient());
        $result = $similarSearchService->search('iPhone');

        $this->assertContainsOnlyInstancesOf(SimilarSearchDto::class, $result->getSimilarSearches());

        $this->assertInstanceOf(SimilarSearchResponse::class, $result);
    }
}
