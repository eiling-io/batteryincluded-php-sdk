<?php
declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Service\SyncService;
use BatteryIncludedSdkTests\Helper;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

$products = Helper::generateProducts(20);

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$transactionId = 'Transaction_' . time();
$syncService = new SyncService($apiClient);

foreach (array_chunk($products, 10) as $productSlice) {
    $result = $syncService->syncFullBatchElements($transactionId, false, ...$productSlice);
    echo '<pre>';
    var_dump($result->getBody());
    echo '</pre>';
}

$result = $syncService->syncFullBatchElements($transactionId, true, ...$productSlice);
echo '<pre>';
var_dump($result->getBody());
echo '</pre>';
exit;
