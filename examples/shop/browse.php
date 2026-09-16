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
    $apiKey,
    $locale
);

$syncService = new BrowseService($apiClient);
$searchStruct = new BrowseSearchStruct();
// the API resolves v[locale] (sent automatically, see setLocale()/ApiClient's default locale) by merging
// the matching _i18n.<locale>._PRODUCT content into _PRODUCT for this request, so filters, sorting and
// reading hits all just use the plain _PRODUCT.* paths below - no _i18n prefix needed on the read side.
$searchStruct->addFilter('_PRODUCT.properties.Speicherkapazität', '512GB');
$searchStruct->setSort('_PRODUCT.price:asc');
$searchStruct->addFilter('_PRODUCT.categories', 'Apple > iPhone > iPhone 20 Pro');
$searchStruct->addFilter('_PRODUCT.properties.Farbe', 'Schwarz');
$searchStruct->addFilter('_PRODUCT.properties.Farbe', 'Blau');
$searchStruct->setQuery('iPhone');
// optional: overrides the ApiClient's configured default locale for this request only
$searchStruct->setLocale('en');
$result = $syncService->browse($searchStruct);

echo '<pre>';
print_r($result->getRawResponse());
echo '</pre>';
exit;
