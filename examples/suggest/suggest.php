<?php

declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Suggest\SuggestSearchStruct;
use BatteryIncludedSdk\Suggest\SuggestService;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$service = new SuggestService($apiClient);
$searchStruct = (new SuggestSearchStruct())
    ->setQuery('Mac')
    ->setLocale('de');
$result = $service->suggestWithFilter($searchStruct);

echo '<pre>';
print_r($result->getQueryCompletions());
echo '</pre>';
echo '<pre>';
print_r($result->getResolvedDocuments());
echo '</pre>';
echo '<pre>';
print_r($result->getFounds());
echo '</pre>';
exit;
