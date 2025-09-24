<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Service;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Product\CategoryDto;
use BatteryIncludedSdk\Product\ProductBaseDto;
use BatteryIncludedSdk\Product\ProductPropertyDto;
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

        $result = $syncService->syncOneOrManyProducts(...$products);
        $this->assertCount(240, $result->getBody());
    }

    public function testSyncFull()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncFull(...$products);
        $this->assertCount(240, $result->getBody());
    }

    public function testSyncFullBatched()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);
        $transactionId = 'Transaction_' . time();

        foreach (array_chunk($products, 10) as $productSlice) {
            $result = $syncService->syncFullBatch($transactionId, false, ...$productSlice);
            $this->assertCount(10, $result->getBody());
        }

        $result = $syncService->syncFullBatch($transactionId, true, ...$productSlice);

        $this->assertCount(10, $result->getBody());
    }

    public function testPartialUpdate()
    {
        $product = new ProductBaseDto();
        $product->setId('239');
        $product->setPrice(13337.95);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->partialUpdateOneOrManyProducts($product);

        $this->assertCount(1, $result->getBody());
    }

    public function testDeleteProduct()
    {
        $this->testSyncFull();
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->deleteProductsByIds('240', '2');

        $this->assertEquals(2, $result->getBody()['num_deleted']);
    }
}
