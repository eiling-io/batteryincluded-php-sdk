<?php

declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\Service\SyncService;
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

// the regular fields act as fallback for every locale without translation
$product = new ProductBaseDto('i18n-1');
$product->setName('Smartphone 17 Pro Blau - 256GB');
$product->setDescription('Das neueste Smartphone mit vielen Funktionen.');
$product->setManufacture('BatteryIncluded');
$product->setPrice(1299.0);
$product->setShopUrl('https://www.batteryincluded.io/de/smartphone-17-pro');
$product->addCategory((new CategoryDto())->addCategoryNode('BatteryIncluded')->addCategoryNode('Handys'));
$product->setProperties(
    (new ProductPropertyDto())
        ->addProperty('Color', 'Blau')
        ->addProperty('Storage', '256GB')
);

// every locale is exported to the _PRODUCT_i18n root node (_PRODUCT_i18n.de), also the language of the regular fields
$product
    ->addTranslation('de', 'name', 'Smartphone 17 Pro Blau - 256GB')
    ->addTranslation('de', 'description', 'Das neueste Smartphone mit vielen Funktionen.')
    ->addCategoryTranslation('de', (new CategoryDto())->addCategoryNode('BatteryIncluded')->addCategoryNode('Handys'))
    ->setPropertiesTranslation(
        'de',
        (new ProductPropertyDto())
            ->addProperty('Color', 'Blau')
            ->addProperty('Storage', '256GB')
    );

// english translations, exported under _PRODUCT_i18n.en
$product
    ->addTranslation('en', 'name', 'Smartphone 17 Pro Blue - 256GB')
    ->addTranslation('en', 'description', 'The latest smartphone with advanced features.')
    ->addTranslation('en', 'shopUrl', 'https://www.batteryincluded.io/en/smartphone-17-pro')
    ->addCategoryTranslation('en', (new CategoryDto())->addCategoryNode('BatteryIncluded')->addCategoryNode('Phones'))
    ->setPropertiesTranslation(
        'en',
        (new ProductPropertyDto())
            ->addProperty('Color', 'Blue')
            ->addProperty('Storage', '256GB')
    );

// partial french translation, the gateway delivers only these fields, all others fall back to the regular values in _PRODUCT
$product->addTranslation('fr', 'name', 'Smartphone 17 Pro Bleu - 256 Go');

echo '<h2>Export</h2><pre>';
echo json_encode($product, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
echo '</pre>';

$syncService = new SyncService($apiClient);
$syncService->syncOneOrManyElements($product);

$browseService = new BrowseService($apiClient);
foreach (['DE', 'EN', 'FR'] as $locale) {
    $searchStruct = new BrowseSearchStruct();
    $searchStruct->setQuery('Smartphone 17 Pro');
    $searchStruct->setPerPage(3);
    $searchStruct->setLocale($locale);
    $result = $browseService->browse($searchStruct);

    echo '<h2>Browse (' . $locale . ')</h2><pre>';
    foreach ($result->getHits() as $hit) {
        // the gateway resolves _PRODUCT_i18n for the requested locale, the sdk merges it into _PRODUCT
        print_r($hit['document']['_PRODUCT']);
    }
    echo '</pre>';
}

echo '<h2>Raw hits (' . $result->getLocale() . ')</h2><pre>';
print_r($result->getRawHits());
echo '</pre>';
