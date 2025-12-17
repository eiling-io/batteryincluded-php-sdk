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
$searchStruct->addFilter('_PRODUCT.properties.Speicherkapazität', '512GB');
$searchStruct->setSort('_PRODUCT.price:asc');
$searchStruct->addFilter('_PRODUCT.categories', 'Apple > iPhone > iPhone 20 Pro');
$searchStruct->addFilter('_PRODUCT.properties.Farbe', 'Schwarz');
$searchStruct->addFilter('_PRODUCT.properties.Farbe', 'Blau');
$searchStruct->setQuery('iPhone');
$searchStruct->setPresetId('857e117c-3766-494d-a692-d7a23c384c33');
$result = $syncService->browse($searchStruct);

echo '<pre>';
print_r($result->getRawResponse());
echo '</pre>';
exit;
