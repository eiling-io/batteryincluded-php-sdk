<?php

declare(strict_types=1);

use BatteryIncludedSdk\Suggest\SuggestSearchStruct;
use BatteryIncludedSdk\Suggest\SuggestService;

require_once __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

$lang = demoResolveLanguage();

$query = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
if (mb_strlen($query) < 2) {
    echo json_encode(['completions' => [], 'products' => [], 'found' => 0]);

    return;
}

$searchStruct = new SuggestSearchStruct();
$searchStruct->setQuery($query);
$searchStruct->setLocale(DEMO_LANGUAGES[$lang]['locale']);

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
    $product = $document['_PRODUCT'] ?? [];
    // the highlighted name is part of the _PRODUCT_i18n node, resolved by the gateway for the requested locale
    $name = (string) ($document['_PRODUCT_i18n']['name'] ?? $product['name'] ?? '');
    $products[] = [
        'html' => highlight($name),
        'image' => (string) ($product['imageUrl'] ?? ''),
        'url' => detailUrl((string) ($document['id'] ?? '')),
        'ordernumber' => strip_tags((string) ($product['ordernumber'] ?? '')),
    ];
}

echo json_encode(['completions' => $completions, 'products' => $products, 'found' => $response->getFounds()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
