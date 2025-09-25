<?php
declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Product\ProductBaseDto;
use BatteryIncludedSdk\Service\SyncService;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

$apiClient = new ApiClient(
    new CurlHttpClient(),
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
exit;
