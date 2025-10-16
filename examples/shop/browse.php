<?php
declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$syncService = new BrowseService($apiClient);
$searchStruct = new BrowseSearchStruct();
$searchStruct->addFilter('properties.Speicherkapazität', '512GB');
$searchStruct->setSort('price:asc');
$searchStruct->addFilter('categories', 'Apple > iPhone > iPhone 20 Pro');
$searchStruct->addFilter('properties.Farbe', 'Schwarz');
$searchStruct->addFilter('properties.Farbe', 'Blau');
$searchStruct->setQuery('iPhone');
$result = $syncService->browse($searchStruct);

echo '<pre>';
print_r($result->getRawResponse());
echo '</pre>';
exit;
