---
lang: en
permalink: /recommendations.html
title: Recommendations
nav_order: 8
---

# Recommendations
{: .no_toc }

1. TOC
{:toc}

## Product recommendations

Recommendations for a product detail page:

```php
use BatteryIncludedSdk\Recommendations\RecommendationsService;

$response = (new RecommendationsService($apiClient))->recommendByIdentifier('1002');

$recommendations = $response->getRecommendations();
$recommendations['related'];   // similar products
$recommendations['also'];      // customers also viewed / bought
$recommendations['together'];  // frequently bought together
```

Pass the identifier of your product (`1002`), the SDK adds the type prefix. For other types use the second argument,
e.g. `recommendByIdentifier('1', 'BLOG')`.

In multi-language shops pass the locale as third argument. The SDK sends it as `v[locale]` and merges the
translations of the locale into the documents, like [`BrowseResponse::getHits()`](translations#reading-translated-hits):

```php
$response = (new RecommendationsService($apiClient))->recommendByIdentifier('1002', 'PRODUCT', 'de_DE');

$response->getRecommendations()['related'][0]['document']['_PRODUCT']['name']; // German name
```

## Cart recommendations

Recommendations for the content of a cart:

```php
use BatteryIncludedSdk\CartRecommendations\CartRecommendationsService;

$response = (new CartRecommendationsService($apiClient))->recommendByIdentifiers(
    ['1002', '1003'],                                    // identifiers in the cart
    ['_PRODUCT.properties.Color' => ['Black']]           // optional filters
);

$response->getRecommendations();   // grouped like the product recommendations
```

The locale is the fourth argument, `recommendByIdentifiers($ids, $filters, 'PRODUCT', 'de_DE')`. It is required to
filter translated fields like `_PRODUCT_i18n.categories`, they are filtered without locale as in the
[search](translations#filtering-and-sorting).

If the api returns no recommendations, both responses contain empty `related`, `also` and `together` lists.

## Presets

List the search presets maintained in the backend, e.g. to offer them in your CMS:

```php
use BatteryIncludedSdk\Presets\PresetsService;

foreach ((new PresetsService($apiClient))->getPresets()->getPresets() as $preset) {
    echo $preset->getId() . ': ' . $preset->getName();
}
```

Use a preset id with [`BrowseSearchStruct::setPresetId()`](search#presets-and-variables). The names of the presets
are not translated, the api delivers them as maintained in the backend.
