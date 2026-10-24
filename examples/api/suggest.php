<?php

declare(strict_types=1);

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\Suggest\SuggestSearchStruct;
use BatteryIncludedSdk\Suggest\SuggestService;

require_once __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$lang = demoResolveLanguage();
// selects the prices of the country and keeps it in the links to the detail page
$country = demoResolveCountry();

$query = mb_trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
if (mb_strlen($query) < 2) {
    echo json_encode(['completions' => [], 'products' => [], 'found' => 0]);

    return;
}

$searchStruct = new SuggestSearchStruct();
$searchStruct->setQuery($query);
$searchStruct->setLocale(DEMO_LANGUAGES[$lang]['locale']);
$searchStruct->setAvailability(DEMO_COUNTRIES[$country]['availability']);

try {
    $response = (new SuggestService(demoApiClient()))->suggestWithFilter($searchStruct);
} catch (Throwable $exception) {
    http_response_code(502);
    echo json_encode(['error' => $exception->getMessage()]);

    return;
}

$completions = [];
foreach (array_slice($response->getQueryCompletions(), 0, 5) as $completion) {
    $completions[] = [
        'value' => $completion->getValue(),
        'html' => highlight($completion->getHighlighted() !== '' ? $completion->getHighlighted() : $completion->getValue()),
    ];
}

$products = [];
foreach (array_slice($response->getDocuments(), 0, 4) as $document) {
    // price (and status) of the country, delivered in `_PRODUCT_availability` if selected under Suggest in the portal
    $product = AvailabilityResolver::resolveDocument($document, $country)['_PRODUCT'] ?? [];
    // the shop id (`_PRODUCT.id`, selected under Suggest in the portal) plus the type is the document id, e.g. PRODUCT-720
    $productId = strip_tags((string) ($product['id'] ?? ''));
    // the highlighted name is part of the _PRODUCT_i18n node, resolved by the gateway for the requested locale
    $name = (string) ($document['_PRODUCT_i18n']['name'] ?? $product['name'] ?? '');
    $products[] = [
        'html' => highlight($name),
        'image' => (string) ($product['imageUrl'] ?? ''),
        'url' => $productId !== '' ? detailUrl('PRODUCT-' . $productId) : '',
        'ordernumber' => strip_tags((string) ($product['ordernumber'] ?? '')),
        'price' => isset($product['price']) ? formatPrice((float) strip_tags((string) $product['price'])) : '',
        'stock' => isset($product['active']) || isset($product['instock']) ? stockStatus($product) : null,
    ];
}

echo json_encode(['completions' => $completions, 'products' => $products, 'found' => $response->getFounds()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
