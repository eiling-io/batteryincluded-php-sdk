<?php
declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\ProductAvailability;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Service\SyncService;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey,
    $locale
);

// A product can be sold on several markets/domains at once. Each market can get its own active
// flag, stock and (optionally) price/categories via addAvailability() - completely independent of
// the _i18n locale content (a market like "de" here has nothing to do with the German language).
$product = new ProductBaseDto('2001');
$product->setId('2001');
$product->setName('Ultra HD HDR LED-TV 55"');
$product->setPrice(699.0); // fallback/base price, used wherever no market override exists
$product->setInstock(0);

$product->addAvailability(new ProductAvailability(
    market: 'de',
    active: true,
    instock: 14,
    price: 699.0,
));
$product->addAvailability(new ProductAvailability(
    market: 'at',
    active: true,
    instock: 3,
    price: 729.0, // higher price for the .at market
));
$product->addAvailability(new ProductAvailability(
    market: 'ch',
    active: false, // not sold on this market at all right now
));

$syncService = new SyncService($apiClient);
$result = $syncService->syncOneOrManyElements($product);

echo '<pre>';
var_dump($result->getBody());
echo '</pre>';
exit;
