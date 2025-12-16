<?php

declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Recommendations\RecommendationsService;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$recommendationService = new RecommendationsService($apiClient);

echo '<pre>';
print_r(json_encode($recommendationService->recommendByIdentifier('19622610')->getRecommendations()));
echo '</pre>';
