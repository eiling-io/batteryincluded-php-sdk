<?php

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

$result = $syncService->deleteProductsByIds('240', '2');

echo '<pre>';
var_dump($result->getBody());
echo '</pre>';
die();