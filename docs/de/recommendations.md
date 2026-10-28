---
lang: de
permalink: /recommendations.html
title: Empfehlungen
nav_order: 8
---

# Empfehlungen
{: .no_toc }

1. TOC
{:toc}

## Produktempfehlungen {#product-recommendations}

Empfehlungen für eine Produktdetailseite:

```php
use BatteryIncludedSdk\Recommendations\RecommendationsService;

$response = (new RecommendationsService($apiClient))->recommendByIdentifier('1002');

$recommendations = $response->getRecommendations();
$recommendations['related'];   // similar products
$recommendations['also'];      // customers also viewed / bought
$recommendations['together'];  // frequently bought together
```

Übergib den Identifier deines Produkts (`1002`), das SDK ergänzt das Typ-Präfix. Für andere Typen nutzt du das zweite
Argument, zum Beispiel `recommendByIdentifier('1', 'BLOG')`.

## Warenkorbempfehlungen {#cart-recommendations}

Empfehlungen zum Inhalt eines Warenkorbs:

```php
use BatteryIncludedSdk\CartRecommendations\CartRecommendationsService;

$response = (new CartRecommendationsService($apiClient))->recommendByIdentifiers(
    ['1002', '1003'],                                    // identifiers in the cart
    ['_PRODUCT.properties.Color' => ['Black']]           // optional filters
);

$response->getRecommendations();   // grouped like the product recommendations
```

Liefert die API keine Empfehlungen, enthalten beide Antworten leere Listen `related`, `also` und `together`.

## Presets {#presets}

Liste die im Backend gepflegten Such-Presets auf, zum Beispiel um sie in deinem CMS anzubieten:

```php
use BatteryIncludedSdk\Presets\PresetsService;

foreach ((new PresetsService($apiClient))->getPresets()->getPresets() as $preset) {
    echo $preset->getId() . ': ' . $preset->getName();
}
```

Eine Preset-ID verwendest du mit [`BrowseSearchStruct::setPresetId()`](search#presets-and-variables).
