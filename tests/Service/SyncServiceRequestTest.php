<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests\Service;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\HttpClientInterface;
use BatteryIncludedSdk\Dto\AbstractAvailabilityDto;
use BatteryIncludedSdk\Dto\AbstractDto;
use BatteryIncludedSdk\Dto\AbstractTranslatableDto;
use BatteryIncludedSdk\Dto\BlogBaseDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Exception\InvalidDocumentException;
use BatteryIncludedSdk\Service\AbstractService;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Service\SyncService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * Checks the requests the SyncService sends, without the live api.
 */
#[CoversClass(SyncService::class)]
#[CoversClass(AbstractService::class)]
#[UsesClass(ApiClient::class)]
#[UsesClass(Response::class)]
#[UsesClass(AbstractDto::class)]
#[UsesClass(AbstractTranslatableDto::class)]
#[UsesClass(AbstractAvailabilityDto::class)]
#[UsesClass(ProductBaseDto::class)]
#[UsesClass(BlogBaseDto::class)]
class SyncServiceRequestTest extends TestCase
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $requests = [];

    public function testImportSendsOneLinePerDocumentWithRootId()
    {
        $product = new ProductBaseDto('1');
        $product->setName('Phone');
        $blog = new BlogBaseDto('1');
        $blog->setTitle('News');

        $this->createSyncService()->syncOneOrManyElements($product, $blog);

        $request = $this->requests[0];
        $this->assertSame('https://api.test/collections/customer.test/documents/import', $request['url']);
        $this->assertSame('POST', $request['method']);
        $this->assertContains('Content-Type: application/x-ndjson', $request['header']);
        $this->assertContains('X-BI-API-KEY: secret', $request['header']);
        $this->assertSame(
            [
                ['id' => 'PRODUCT-1', 'type' => 'PRODUCT', '_PRODUCT' => ['name' => 'Phone']],
                ['id' => 'BLOG-1', 'type' => 'BLOG', '_BLOG' => ['title' => 'News']],
            ],
            $this->decodeNdjson($request['data'])
        );
    }

    public function testFullImport()
    {
        $this->createSyncService()->syncFullElements(new ProductBaseDto('1'));

        $this->assertSame('https://api.test/collections/customer.test/documents/import?full=1', $this->requests[0]['url']);
        $this->assertSame('POST', $this->requests[0]['method']);
    }

    public function testBatchedFullImportCompletesTransactionOnlyWithLastBatch()
    {
        $syncService = $this->createSyncService();

        $syncService->syncFullBatchElements('tx-1', false, new ProductBaseDto('1'));
        $syncService->syncFullBatchElements('tx-1', true, new ProductBaseDto('2'));

        $this->assertSame('https://api.test/collections/customer.test/documents/import?transactionId=tx-1', $this->requests[0]['url']);
        $this->assertSame(
            'https://api.test/collections/customer.test/documents/import?transactionId=tx-1&transactionCompleted=1',
            $this->requests[1]['url']
        );
        $this->assertSame([['id' => 'PRODUCT-2', 'type' => 'PRODUCT', '_PRODUCT' => []]], $this->decodeNdjson($this->requests[1]['data']));
    }

    public function testPartialUpdateSendsOnlySetFields()
    {
        $product = new ProductBaseDto('239');
        $product->setPrice(13337.95);

        $this->createSyncService()->partialUpdateOneOrManyElements($product);

        $this->assertSame('https://api.test/collections/customer.test/documents/update', $this->requests[0]['url']);
        $this->assertSame('PATCH', $this->requests[0]['method']);
        $this->assertSame(
            [['id' => 'PRODUCT-239', 'type' => 'PRODUCT', '_PRODUCT' => ['price' => 13337.95]]],
            $this->decodeNdjson($this->requests[0]['data'])
        );
    }

    public function testDeleteSendsRootIds()
    {
        $this->createSyncService()->deleteElementsByIds('PRODUCT-240', 'BLOG-2');

        $this->assertSame('https://api.test/collections/customer.test/documents/delete', $this->requests[0]['url']);
        $this->assertSame('DELETE', $this->requests[0]['method']);
        $this->assertContains('Content-Type: application/json', $this->requests[0]['header']);
        $this->assertSame('["PRODUCT-240","BLOG-2"]', $this->requests[0]['data']);
    }

    public function testTransactionIdIsUrlEncoded()
    {
        $this->createSyncService()->syncFullBatchElements('tx 1&full=1', true, new ProductBaseDto('1'));

        $this->assertSame(
            'https://api.test/collections/customer.test/documents/import?transactionId=tx+1%26full%3D1&transactionCompleted=1',
            $this->requests[0]['url']
        );
    }

    public function testInvalidUtf8IsReplacedInsteadOfSendingAnEmptyLine()
    {
        $product = new ProductBaseDto('1');
        $product->setName("Caf\xE9 Crème");

        $this->createSyncService()->syncOneOrManyElements($product);

        $this->assertSame("Caf\u{FFFD} Crème", $this->decodeNdjson($this->requests[0]['data'])[0]['_PRODUCT']['name']);
        // unescaped unicode and slashes keep the payload small
        $this->assertStringContainsString('"Caf' . "\u{FFFD}" . ' Crème"', $this->requests[0]['data']);
    }

    public function testUnencodableDocumentThrowsWithItsId()
    {
        $product = new ProductBaseDto('7');
        $product->setPrice(NAN);

        $this->expectException(InvalidDocumentException::class);
        $this->expectExceptionMessage('Document PRODUCT-7 cannot be encoded');

        $this->createSyncService()->syncOneOrManyElements(new ProductBaseDto('1'), $product);
    }

    private function createSyncService(): SyncService
    {
        // records the requests instead of sending them
        $httpClient = new class(function (array $request) {
            $this->requests[] = $request;
        }) implements HttpClientInterface {
            public function __construct(private \Closure $record)
            {
            }

            public function send(string $url, string $method, array $header, string $data): Response
            {
                ($this->record)(['url' => $url, 'method' => $method, 'header' => $header, 'data' => $data]);

                return new Response('[]', 200);
            }
        };

        return new SyncService(new ApiClient($httpClient, 'https://api.test/collections/', 'customer.test', 'secret'));
    }

    private function decodeNdjson(string $data): array
    {
        $lines = array_filter(explode("\n", $data), static fn (string $line) => $line !== '');

        return array_map(static fn (string $line) => json_decode($line, true), array_values($lines));
    }
}
