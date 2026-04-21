<?php

declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Service\SyncService;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

class ProductDto extends ProductBaseDto
{
    private ?string $keywords = null;

    public function jsonSerialize(): array
    {
        $jsonDto = [
            'keywords' => $this->getKeywords(),
        ];

        $jsonRaw = array_merge_recursive(
            parent::jsonSerialize(),
            ['_' . $this->getType() => array_filter($jsonDto, static fn ($value) => $value !== null)]
        );

        return $jsonRaw;
    }

    private function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function setKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }
}

$storages = ['128GB', '256GB', '512GB'];

$id = 0;
foreach ($storages as $storage) {
    $id++;
    $product = new ProductDto((string) $id);
    $product->setName('Storage: ' . $storage);
    $product->setDescription(
        'Storage: ' . $storage . '.'
    );
    $product->setId((string) $id);
    $product->setOrdernumber('AP-00' . $id . '-' . $storage);
    $product->setPrice(1000 + $id);
    $product->setInstock(random_int(0, 50));
    $product->setRating((float) (random_int(1, 10) / 2));
    $product->setManufacture('Apple');
    $product->setManufactureNumber('A' . $id . '-' . $storage);

    $products[] = $product;
}

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$syncService = new SyncService($apiClient);

$result = $syncService->syncFullElements(...$products);

echo '<pre>';
var_dump($result->getBody());
echo '</pre>';
exit;
