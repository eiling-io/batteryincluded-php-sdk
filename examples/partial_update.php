<?php

use BatteryIncludedSdk\Product\ProductBaseDto;
use BatteryIncludedSdk\Service\ApiClient;
use BatteryIncludedSdk\Service\SyncService;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/credentials.php';

$apiClient = new ApiClient(
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$syncService = new SyncService($apiClient);

$product = new ProductBaseDto();
$product->setId('239');
$product->setPrice(13337.95);
$result = $syncService->partialUpdateOneOrManyProducts($product);

echo '<pre>';
var_dump($result->getBody());
echo '</pre>';
die();