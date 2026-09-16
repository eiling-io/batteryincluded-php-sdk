<?php
declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\Dto\ProductTranslation;
use BatteryIncludedSdk\Service\SyncService;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey,
    $locale // 'de' by default, see credentials.php
);

// 1) single language: the existing setters (setName, setDescription, addCategory, setProperties, ...)
// still work unchanged. Without any locale() call, they're written to the ApiClient's default locale
// ($locale, i.e. "de") when the product is synced.
$singleLanguageProduct = new ProductBaseDto('1001');
$singleLanguageProduct->setId('1001');
$singleLanguageProduct->setName('Ultra HD HDR LED-TV 75"');
$singleLanguageProduct->setDescription('Ultra HD HDR LED-TV 75" (189 cm)');
$singleLanguageProduct->setPrice(899.0);
$singleLanguageProduct->setShopUrl('https://shop.example/tv-75-hdr');

// 2) multiple languages on one product: pin the locale the flat setters write to, then add
// further languages via addTranslation(). shopUrl() is reused for every locale's "url" unless a
// translation sets its own - handy when the product page is the same across languages, and still
// overridable when a locale has its own landing page.
// categories and properties are what the API's category/select facets are built from (see
// BrowseResponse::getFacets()), so translating them here is what makes facet labels come back
// in the requested language too - there's no separate "facet sync", it's driven by this same data.
$multiLanguageProduct = new ProductBaseDto('1002');
$multiLanguageProduct->setId('1002');
$multiLanguageProduct->setShopUrl('https://shop.example/tv-65-hdr');
$multiLanguageProduct->locale('de');
$multiLanguageProduct->setName('Ultra HD HDR LED-TV 65"');
$multiLanguageProduct->setDescription('Ultra HD HDR LED-TV 65" (165 cm)');
$multiLanguageProduct->setProperties(
    (new ProductPropertyDto())->addProperty('Farbe', 'Schwarz')
);
$multiLanguageProduct->addCategory(
    (new CategoryDto())->addCategoryNode('Elektronik')->addCategoryNode('Fernseher')
);

$multiLanguageProduct->addTranslation('en', new ProductTranslation(
    name: 'Ultra HD HDR LED TV 65"',
    description: 'Ultra HD HDR LED TV 65" (65 inch)',
    categories: (new CategoryDto())->addCategoryNode('Electronics')->addCategoryNode('TVs')->jsonSerialize(),
    properties: (new ProductPropertyDto())->addProperty('Colour', 'Black'),
));
$multiLanguageProduct->addTranslation('fr', new ProductTranslation(
    name: 'TV LED HDR Ultra HD 65"',
    description: 'TV LED HDR Ultra HD 65" (165 cm)',
    categories: (new CategoryDto())->addCategoryNode('Électronique')->addCategoryNode('Téléviseurs')->jsonSerialize(),
    properties: (new ProductPropertyDto())->addProperty('Couleur', 'Noir'),
    url: 'https://shop.example/fr/tv-65-hdr', // this locale gets its own URL instead of shopUrl()
));

$syncService = new SyncService($apiClient);
$result = $syncService->syncOneOrManyElements($singleLanguageProduct, $multiLanguageProduct);

echo '<pre>';
var_dump($result->getBody());
echo '</pre>';
exit;
