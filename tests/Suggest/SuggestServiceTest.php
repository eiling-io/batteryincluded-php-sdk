<?php

declare(strict_types=1);

namespace Suggest;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Service\AbstractService;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Service\SyncService;
use BatteryIncludedSdk\Shop\FacetDto;
use BatteryIncludedSdk\Shop\FacetRangeDto;
use BatteryIncludedSdk\Shop\FacetSelectDto;
use BatteryIncludedSdk\Shop\FacetValueDto;
use BatteryIncludedSdk\Suggest\CompletionDto;
use BatteryIncludedSdk\Suggest\SuggestResponse;
use BatteryIncludedSdk\Suggest\SuggestSearchStruct;
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
#[UsesClass(SuggestSearchStruct::class)]
#[UsesClass(I18nResolver::class)]
#[UsesClass(AvailabilityResolver::class)]
class SuggestServiceTest extends TestCase
{
    public function testSuggestSendsLocale()
    {
        $searchStruct = new SuggestSearchStruct();
        $searchStruct->setLocale('EN');

        $this->assertSuggestUrlContains(
            static fn (SuggestService $service) => $service->suggestWithFilter($searchStruct),
            'v%5Blocale%5D=en'
        );
        $this->assertSuggestUrlContains(static fn (SuggestService $service) => $service->suggest('iPhone'), 'v%5Blocale%5D=de');
    }

    public function testSuggestSendsAvailabilityOnlyWhenSet()
    {
        $searchStruct = new SuggestSearchStruct();
        $searchStruct->setAvailability('CH');

        $this->assertSuggestUrlContains(
            static fn (SuggestService $service) => $service->suggestWithFilter($searchStruct),
            'v%5Bavailability%5D=ch'
        );
        $this->assertSuggestUrlContains(
            static fn (SuggestService $service) => $service->suggestWithFilter(new SuggestSearchStruct()),
            'v%5Blocale%5D=de',
            'availability'
        );
    }

    private function assertSuggestUrlContains(callable $suggest, string $expected, ?string $unexpected = null): void
    {
        $mockResponse = $this->createMock(Response::class);
        $mockResponse->method('getRawResponse')->willReturn('[]');
        $mockResponse->method('getStatusCode')->willReturn(200);

        $mockApiClient = $this->createMock(ApiClient::class);
        $mockApiClient->expects($this->once())
            ->method('getJson')
            ->with($this->callback(fn ($url) => str_contains($url, $expected) && ($unexpected === null || !str_contains($url, $unexpected))), [])
            ->willReturn($mockResponse);

        $this->assertInstanceOf(SuggestResponse::class, $suggest(new SuggestService($mockApiClient)));
    }

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

    public function testSuggestWithFilterMethodAgainstLiveApi()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(720, $result->getBody());
        $browseService = new SuggestService(Helper::getApiClient());
        $searchStruct = new SuggestSearchStruct();
        $searchStruct->setQuery('12');
        $searchStruct->addFilter('type', 'PRODUCT');
        $result = $browseService->suggestWithFilter($searchStruct);

        $this->assertContainsOnlyInstancesOf(CompletionDto::class, $result->getQueryCompletions());

        $this->assertInstanceOf(SuggestResponse::class, $result);
        $this->assertGreaterThanOrEqual(12, $result->getFounds());
        $this->assertCount(6, $result->getDocuments());
        $this->assertFalse($result->isLLM());
    }
}
