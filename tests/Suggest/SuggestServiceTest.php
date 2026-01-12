<?php

declare(strict_types=1);

namespace Suggest;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\Service\AbstractService;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Service\SyncService;
use BatteryIncludedSdk\Shop\FacetDto;
use BatteryIncludedSdk\Shop\FacetRangeDto;
use BatteryIncludedSdk\Shop\FacetSelectDto;
use BatteryIncludedSdk\Shop\FacetValueDto;
use BatteryIncludedSdk\Suggest\CompletionDto;
use BatteryIncludedSdk\Suggest\SuggestResponse;
use BatteryIncludedSdk\Suggest\SuggestService;
use BatteryIncludedSdkTests\Helper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[UsesClass(CompletionDto::class)]
#[CoversClass(SuggestResponse::class)]
#[CoversClass(SuggestService::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(CurlHttpClient::class)]
#[UsesClass(Response::class)]
#[UsesClass(CategoryDto::class)]
#[UsesClass(FacetDto::class)]
#[UsesClass(FacetRangeDto::class)]
#[UsesClass(FacetSelectDto::class)]
#[UsesClass(FacetValueDto::class)]
#[UsesClass(ProductBaseDto::class)]
#[UsesClass(ProductPropertyDto::class)]
#[UsesClass(AbstractService::class)]
#[UsesClass(SyncService::class)]
class SuggestServiceTest extends TestCase
{
    public function testSuggestMethodAgainstLiveApi()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(720, $result->getBody());
        $browseService = new SuggestService(Helper::getApiClient());
        $result = $browseService->suggest('12');

        $this->assertContainsOnlyInstancesOf(CompletionDto::class, $result->getQueryCompletions());

        $this->assertInstanceOf(SuggestResponse::class, $result);
        $this->assertGreaterThanOrEqual(12, $result->getFounds());
        $this->assertCount(6, $result->getDocuments());
        $this->assertFalse($result->isLLM());
    }
}
