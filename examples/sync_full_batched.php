<?php

use BatteryIncludedSdk\Product\CategoryDto;
use BatteryIncludedSdk\Product\ProductBaseDto;
use BatteryIncludedSdk\Product\ProductPropertyDto;
use BatteryIncludedSdk\Service\ApiClient;
use BatteryIncludedSdk\Service\SyncService;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/credentials.php';

$products = [];
$id = 0;
for ($i = 1; $i <= 20; $i++) {
    foreach (['Blau', 'Rosa', 'Gold', 'Schwarz',] as $color) {
        foreach (['128GB', '256GB', '512GB'] as $storage) {
            $id++;
            $product = new ProductBaseDto();
            $product->setName('iPhone ' . $i . ' Pro ' . $color . ' - ' . $storage);
            $product->setDescription(
                'The latest iPhone with advanced features. Color: ' . $color . ', Storage: ' . $storage . '.'
            );
            $product->setId($id);
            $product->setOrdernumber('AP-00' . $i . '-' . $color . '-' . $storage);
            $product->setPrice(999.99);
            $product->setInstock(50);
            $product->setManufacture('Apple');
            $product->setManufactureNumber('A' . $i . '-' . $color . '-' . $storage);
            $product->setEan('195950639292');
            $product->setImageUrl(
                'https://dummyimage.com/600x400/bbb/fff.png&text=' . $i . '-' . $color . '-' . $storage
            );
            $product->setShopUrl('https://www.apple.com/iphone-17-pro/');
            $product->setProperties(
                (new ProductPropertyDto())
                    ->addProperty('Farbe', $color)
                    ->addProperty('Speicherkapazität', $storage)
                    ->addProperty('Displaygröße', '6,1 Zoll')
            );
            $product->addCategory(
                (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode('iPhone')->addCategoryNode(
                    'iPhone ' . $i . ' Pro'
                )
            );
            $product->addCategory(
                (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode('iPhone Pro ' . $color)
            );
            $products[] = $product;
        }
    }
}


$apiClient = new ApiClient(
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$transactionId = 'Transaction_' . time();
$syncService = new SyncService($apiClient);

foreach (array_chunk($products, 10) as $productSlice) {
    $result = $syncService->syncFullBatch($transactionId, false, ...$productSlice);
    echo '<pre>';
    var_dump($result->getBody());
    echo '</pre>';
}

$result = $syncService->syncFullBatch($transactionId, true, ...$productSlice);
echo '<pre>';
var_dump($result->getBody());
echo '</pre>';
die();