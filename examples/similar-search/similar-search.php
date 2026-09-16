<?php

declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\SimilarSearch\SimilarSearchService;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey,
    $locale
);

$similarService = new SimilarSearchService($apiClient);

echo '<pre>';
// no locale argument needed: falls back to the ApiClient's configured default ($locale above).
// pass one explicitly, e.g. search('Apple', 'en'), to override it for a single request.
print_r($similarService->search('Apple')->getSimilarSearches());
echo '</pre>';
