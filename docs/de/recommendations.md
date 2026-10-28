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

In mehrsprachigen Shops übergibst du die Locale als drittes Argument. Das SDK sendet sie als `v[locale]` und führt die
Übersetzungen der Locale in die Dokumente zusammen, wie [`BrowseResponse::getHits()`](translations#reading-translated-hits):

```php
$response = (new RecommendationsService($apiClient))->recommendByIdentifier('1002', 'PRODUCT', 'de_DE');

$response->getRecommendations()['related'][0]['document']['_PRODUCT']['name']; // German name
```

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

Die Locale ist das vierte Argument, `recommendByIdentifiers($ids, $filters, 'PRODUCT', 'de_DE')`. Du brauchst sie, um
übersetzte Felder wie `_PRODUCT_i18n.categories` zu filtern; sie werden wie bei der
[Suche](translations#filtering-and-sorting) ohne Locale gefiltert.

Liefert die API keine Empfehlungen, enthalten beide Antworten leere Listen `related`, `also` und `together`.

## Presets {#presets}

Liste die im Backend gepflegten Such-Presets auf, zum Beispiel um sie in deinem CMS anzubieten:

```php
use BatteryIncludedSdk\Presets\PresetsService;

foreach ((new PresetsService($apiClient))->getPresets()->getPresets() as $preset) {
    echo $preset->getId() . ': ' . $preset->getName();
}
```

Eine Preset-ID verwendest du mit [`BrowseSearchStruct::setPresetId()`](search#presets-and-variables). Die Namen der
Presets sind nicht übersetzt, die API liefert sie so, wie sie im Backend gepflegt sind.
