---
lang: de
permalink: /search.html
title: Suche & Filter
nav_order: 6
---

# Suche & Filter
{: .no_toc }

1. TOC
{:toc}

`BrowseService` steckt hinter Suchergebnisseiten und Kategorielisten: Volltextsuche, Filter, Sortierung, Blättern,
Facetten und Merchandising-Extensions in einer Anfrage.

## Einfache Suche {#basic-search}

```php
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;

$searchStruct = new BrowseSearchStruct();
$searchStruct->setQuery('smartphone');
$searchStruct->setLocale('en');
$searchStruct->setPerPage(24);   // default 10
$searchStruct->setPage(2);       // default 1

$result = (new BrowseService($apiClient))->browse($searchStruct);

$result->getFound();  // total number of hits
$result->getPage();   // current page
$result->getPages();  // number of pages
```

Ohne Suchbegriff (oder mit `*`) kommen alle Dokumente zurück, genau das brauchst du für Kategorieseiten.

## Treffer auslesen {#reading-hits}

```php
foreach ($result->getHits() as $hit) {
    $document = $hit['document'];

    match ($document['type']) {
        'PRODUCT' => printf("%s – %.2f €\n", $document['_PRODUCT']['name'], $document['_PRODUCT']['price']),
        'BLOG' => printf("%s by %s\n", $document['_BLOG']['title'], $document['_BLOG']['author']),
    };
}
```

Jeder Treffer enthält außerdem `highlight` mit den gefundenen Tokens des Suchbegriffs. Wie sich `getHits()` und
`getRawHits()` unterscheiden, steht unter [Übersetzungen](translations#reading-translated-hits).

## Filter {#filters}

```php
// several values of one field are combined with OR
$searchStruct->addFilter('_PRODUCT.properties.Color', 'Black');
$searchStruct->addFilter('_PRODUCT.properties.Color', 'Blue');

// different fields are combined with AND
$searchStruct->addFilter('_PRODUCT.properties.Storage', '512GB');

// a category filter includes all subcategories
$searchStruct->addFilter('_PRODUCT.categories', 'Electronics > TVs');

// ranges
$searchStruct->addRangeFilter('_PRODUCT.price', '100', '500');

// filters from a request, e.g. ?f[_PRODUCT.properties.Color][]=Black&f[_PRODUCT.price][from]=100
$searchStruct->addFilters($_GET['f'] ?? []);
```

### Nach Typ filtern {#filtering-by-type}

Eine Collection kann Produkte und Blogbeiträge mischen. Schränke das Ergebnis mit dem Filter `type` ein:

```php
$searchStruct->addFilter('type', 'PRODUCT');
```

### Ein einzelnes Dokument laden {#fetching-a-single-document}

Filtere nach der Dokument-ID, um ein einzelnes Dokument zu laden, zum Beispiel für eine Detailseite:

```php
$searchStruct = new BrowseSearchStruct();
$searchStruct->addFilter('id', 'PRODUCT-1002');
$searchStruct->setPerPage(1);

$document = (new BrowseService($apiClient))->browse($searchStruct)->getHits()[0]['document'] ?? null;
```

## Sortierung {#sorting}

```php
$searchStruct->setSort('_PRODUCT.price:asc');
$searchStruct->setSort('_PRODUCT.rating:desc');
```

Ohne Sortierung ist das Ergebnis nach Relevanz geordnet.

## Presets und Variablen {#presets-and-variables}

Ein Preset bündelt Sucheinstellungen, die im Backend gepflegt werden (zum Beispiel Ranking-Regeln für eine
Landingpage):

```php
$searchStruct->setPresetId('857e117c-3766-494d-a692-d7a23c384c33');
```

Die verfügbaren Presets listet der [`PresetsService`](recommendations#presets) auf.

Variablen gehen als `v[name]` an das Gateway. `locale` setzt `setLocale()`, andere definierst du in deiner Collection:

```php
$searchStruct->addVariable('currency', 'EUR');
```

## Facetten {#facets}

Facetten gibt es nur für Felder, die [im Backend als Filter konfiguriert](backend-configuration#filters) sind.
`getFacets()` macht aus den Facetten-Zählungen typisierte Objekte. Übergib das Feld, das deinen Kategoriebaum enthält,
zum Beispiel `_PRODUCT_i18n.categories` für [übersetzte Kategorien](translations#filtering-and-sorting):

```php
use BatteryIncludedSdk\Shop\FacetCategoryDto;
use BatteryIncludedSdk\Shop\FacetRangeDto;
use BatteryIncludedSdk\Shop\FacetRatingDto;
use BatteryIncludedSdk\Shop\FacetSelectDto;

foreach ($result->getFacets('_PRODUCT.categories') as $facet) {
    echo $facet->getFieldLabel();   // label from the backend, e.g. "Color"
    $facet->getFieldName();         // filter key, e.g. "_PRODUCT.properties.Color"
    $facet->getFieldUnit();         // e.g. "€"

    if ($facet instanceof FacetSelectDto) {
        foreach ($facet->getValues() as $value) {
            printf("[%s] %s (%d)\n", $value->isChecked() ? 'x' : ' ', $value->getName(), $value->getCount());
        }
    } elseif ($facet instanceof FacetRangeDto) {
        printf("%s – %s, selected %s – %s\n", $facet->getMin(), $facet->getMax(), $facet->getSelectedMin(), $facet->getSelectedMax());
    } elseif ($facet instanceof FacetRatingDto) {
        $facet->getRatings();       // FacetValueDto[] per star rating
    } elseif ($facet instanceof FacetCategoryDto) {
        $facet->getCategories();    // nested tree: ['Electronics' => ['count' => 12, 'checked' => true, 'childs' => [...]]]
    }
}
```

Der Zustand `checked` spiegelt die Filter des Search Structs wider, so renderst du die Seitenleiste direkt aus der
Antwort. Facetten ohne Treffer im aktuellen Ergebnis werden übersprungen, die API liefert sie ohne Typ aus.

## Extensions {#extensions}

Im Backend konfigurierte Merchandising-Regeln kommen als Extensions zurück:

| Methode | Inhalt |
|---|---|
| `getTeaserExtensions()` | Banner zwischen den Treffern (`position`, `image`, `url`) |
| `getPromotionsExtensions()` | Banner über dem Ergebnis |
| `getRedirectsExtensions()` | Eine URL für eine Weiterleitung, zum Beispiel beim Suchbegriff „Kontakt“ |
| `getCodesExtensions()` | Ein Aktionscode, der zum Suchbegriff passt |
| `getAllExtensions()` | Alle oben genannten |

```php
foreach ($result->getRedirectsExtensions() as $redirect) {
    header('Location: ' . $redirect->getData()['url']);
    exit;
}
```
