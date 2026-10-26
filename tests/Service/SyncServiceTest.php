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
use BatteryIncludedSdk\Shop\BrowseResponse;
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;
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
#[UsesClass(BrowseService::class)]
#[UsesClass(BrowseSearchStruct::class)]
#[UsesClass(BrowseResponse::class)]
class SyncServiceTest extends TestCase
{
    public function testSyncOneOrMany()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncOneOrManyElements(...$products);
        $this->assertCount(720, $result->getBody());
    }

    public function testSyncFull()
    {
        $products = Helper::generateProducts(20);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->syncFullElements(...$products);
        $this->assertCount(720, $result->getBody());
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

    public function testSyncedDocumentHasIdOnlyInRoot()
    {
        $this->syncProduct239();

        $document = $this->loadDocument('PRODUCT-239');

        $this->assertSame('PRODUCT-239', $document['id']);
        $this->assertSame('PRODUCT', $document['type']);
        $this->assertArrayNotHasKey('id', $document['_PRODUCT']);
    }

    public function testPartialUpdate()
    {
        $this->syncProduct239();
        $before = $this->loadDocument('PRODUCT-239')['_PRODUCT'];

        $product = new ProductBaseDto('239');
        $product->setPrice(13337.95);
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->partialUpdateOneOrManyElements($product);

        $this->assertCount(1, $result->getBody());

        // the index is updated asynchronously, the change is visible after a few seconds
        $after = $this->loadDocument('PRODUCT-239', static fn (array $document) => (float) $document['_PRODUCT']['price'] === 13337.95)['_PRODUCT'];
        $this->assertEquals(13337.95, $after['price']);
        // only the price changes, the other fields stay
        $this->assertSame($before['name'], $after['name']);
        $this->assertSame($before['ordernumber'], $after['ordernumber']);
        $this->assertSame($before['categories'], $after['categories']);
    }

    public function testDeleteProduct()
    {
        $this->testSyncFull();
        $apiClient = Helper::getApiClient();
        $syncService = new SyncService($apiClient);

        $result = $syncService->deleteElementsByIds('PRODUCT-720', 'PRODUCT-2');

        $this->assertEquals(2, $result->getBody()['num_deleted']);
    }

    /**
     * Resets PRODUCT-239 to the generated test data, a full import of all products takes too long.
     */
    private function syncProduct239(): void
    {
        $product = Helper::generateProducts(20)[238];
        $this->assertSame('PRODUCT-239', $product->getIdentifier());

        $result = (new SyncService(Helper::getApiClient()))->syncOneOrManyElements($product);
        $this->assertTrue($result->getBody()[0]['success']);

        // wait until the reset price is indexed, the partial update test changes it
        $this->loadDocument('PRODUCT-239', static fn (array $document) => (float) $document['_PRODUCT']['price'] === $product->getPrice());
    }

    /**
     * @param callable(array): bool|null $until polls until the document matches, at most 30 seconds
     */
    private function loadDocument(string $id, ?callable $until = null): array
    {
        $searchStruct = new BrowseSearchStruct();
        $searchStruct->addFilter('id', $id);
        $searchStruct->setPerPage(1);
        $browseService = new BrowseService(Helper::getApiClient());
        $deadline = microtime(true) + 30;

        do {
            $hits = $browseService->browse($searchStruct)->getRawHits();
            $this->assertCount(1, $hits, 'document ' . $id . ' not found');
            if ($until === null || $until($hits[0]['document'])) {
                break;
            }
            usleep(500000);
        } while (microtime(true) < $deadline);

        return $hits[0]['document'];
    }
}
