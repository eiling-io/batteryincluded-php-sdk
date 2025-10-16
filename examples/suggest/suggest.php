<?php

declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
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
$result = $service->suggest('12');

echo '<pre>';
print_r($result->getQueryCompletions());
echo '</pre>';
echo '<pre>';
print_r($result->getDocuments());
echo '</pre>';
echo '<pre>';
print_r($result->getFounds());
echo '</pre>';
exit;
