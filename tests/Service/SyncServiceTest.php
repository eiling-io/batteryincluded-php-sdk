<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Service;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Service\SyncService;
use BatteryIncludedSdkTests\Helper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ApiClient::class)]
#[CoversClass(CurlHttpClient::class)]
#[CoversClass(SyncService::class)]
#[CoversClass(Response::class)]
#[UsesClass(CategoryDto::class)]
#[UsesClass(ProductBaseDto::class)]
#[UsesClass(ProductPropertyDto::class)]
class SyncServiceTest extends TestCase
{
    public function testSyncOneOrMany()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(240, $result->getBody());
    }

    public function testSyncFull()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncFullElements(...$products);
        $this->assertCount(240, $result->getBody());
    }

    public function testSyncFullBatched()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);
        $transactionId = 'Transaction_' . time();
        $productSlice = [];

        foreach (array_chunk($products, 10) as $productSlice) {
            $result = $syncService->syncFullBatchElements($transactionId, false, ...$productSlice);
            $this->assertCount(10, $result->getBody());
        }

        $result = $syncService->syncFullBatchElements($transactionId, true, ...$productSlice);

        $this->assertCount(10, $result->getBody());
    }

    public function testPartialUpdate()
    {
        $product = new ProductBaseDto('239');
        $product->setId('239');
        $product->setPrice(13337.95);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->partialUpdateOneOrManyElements($product);

        $this->assertCount(1, $result->getBody());
    }

    public function testDeleteProduct()
    {
        $this->testSyncFull();
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->deleteElementsByIds('PRODUCT-240', 'PRODUCT-2');

        $this->assertEquals(2, $result->getBody()['num_deleted']);
    }
}
