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

If the api returns no recommendations, both responses contain empty `related`, `also` and `together` lists.

## Presets

List the search presets maintained in the backend, e.g. to offer them in your CMS:

```php
use BatteryIncludedSdk\Presets\PresetsService;

foreach ((new PresetsService($apiClient))->getPresets()->getPresets() as $preset) {
    echo $preset->getId() . ': ' . $preset->getName();
}
```

Use a preset id with [`BrowseSearchStruct::setPresetId()`](search#presets-and-variables).
