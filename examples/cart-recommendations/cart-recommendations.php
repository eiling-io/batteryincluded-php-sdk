<?php

declare(strict_types=1);

use BatteryIncludedSdk\CartRecommendations\CartRecommendationsService;
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$cartRecommendationService = new CartRecommendationsService($apiClient);

echo '<pre>';
print_r(json_encode($cartRecommendationService->recommendByIdentifiers(['n2000002058', 'n1000224412'])->getRecommendations()));
echo '</pre>';
