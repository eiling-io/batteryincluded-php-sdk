---
lang: de
permalink: /availability.html
title: Verfügbarkeit
nav_order: 4.5
---

# Verfügbarkeit
{: .no_toc }

1. TOC
{:toc}

Status, Preis und Bestand eines Produkts können sich je Land oder Vertriebskanal unterscheiden. Wie bei den
Übersetzungen werden alle Scopes in einem Dokument exportiert, und das Gateway liefert nur den angefragten aus. Das
Modul arbeitet neben den [Übersetzungen](translations) und verändert sie nicht.

## Dokumentschema {#document-schema}

Die Standardwerte bleiben in `_PRODUCT`, die Werte je Scope liegen in `_PRODUCT_availability.<scope>`:

```json
{
  "id": "PRODUCT-1002",
  "type": "PRODUCT",
  "_PRODUCT_availability": {
    "de": {"active": true, "price": 1337.0},
    "ch": {"active": false, "instock": 0}
  },
  "_PRODUCT": {
    "name": "Ultra HD LED-TV 65\"",
    "price": 999.0,
    "active": true,
    "instock": 14
  }
}
```

## Collection einrichten {#collection-setup}

Lege im BatteryIncluded Backend eine zweite Variable an:

```json
{"name": "availability", "default": "de", "root": ["_PRODUCT_availability"]}
```

## Verfügbarkeiten exportieren {#exporting-availabilities}

```php
use BatteryIncludedSdk\Dto\ProductBaseDto;

$product = new ProductBaseDto('1002');
$product->setPrice(999.0);      // default values
$product->setActive(true);
$product->setInstock(14);

$product
    ->setAvailability('de', active: true, price: 1337.0)
    ->setAvailability('ch', active: false, instock: 0)
    ->addAvailability('at', 'price', 1029.0);
```

| Methode | Beschreibung |
|---|---|
| `setAvailability($scope, active:, price:, instock:)` | Mehrere Felder eines Scopes setzen, `null` behält den Standardwert |
| `addAvailability($scope, $field, $value)` | Ein einzelnes Feld eines Scopes setzen |
| `getAvailability($scope)` / `getAvailabilities()` | Die Verfügbarkeiten wieder auslesen |

- `ProductBaseDto` akzeptiert die Felder `active`, `price` und `instock`, andere Felder werfen eine
  `InvalidArgumentException`. Eigene DTOs erweitern `AbstractAvailabilityDto` und überschreiben
  `getAvailabilityFields()`.
- Scopes werden wie Locales normalisiert: Aus `DE` wird `de`, aus `b2b_DE` wird `b2b-de`.
- `false` und `0` überschreiben den Standardwert (inaktiv, nicht vorrätig), `null` und leere Werte nicht.

## Einen Scope anfragen {#requesting-a-scope}

```php
$searchStruct = new BrowseSearchStruct();
$searchStruct->setLocale('de');
$searchStruct->setAvailability('ch');      // or $searchStruct->addVariable('availability', 'ch')
```

- Ohne `setAvailability()` sendet das SDK kein `v[availability]`, es gilt der `default` der Variable.
- Zwischen den Scopes gibt es keinen Fallback: Fehlt einem Dokument der angefragte Scope, gelten die Standardwerte.

## Treffer auslesen {#reading-hits}

`BrowseResponse::getHits()` führt zuerst die Übersetzungen und danach die Verfügbarkeit in `_PRODUCT` zusammen. Bei
Feldern, die in beiden Knoten stehen, gewinnt also die Verfügbarkeit:

```php
$result = (new BrowseService($apiClient))->browse($searchStruct);

foreach ($result->getHits() as $hit) {
    $product = $hit['document']['_PRODUCT'];
    echo $product['active'] ? $product['price'] : 'not available';
}

$result->getRawHits();      // untouched response including _PRODUCT_availability
$result->getAvailability(); // requested scope, null for the default
```

Mit `AvailabilityResolver::resolveDocument($document, $scope)` löst du Dokumente aus anderen Quellen auf dieselbe Weise
auf.

## Vorschläge {#suggestions}

`SuggestSearchStruct::setAvailability()` funktioniert genauso. Der Suggest-Endpunkt liefert nur die Felder, die im
Portal unter Suggest ausgewählt sind. Wähle `price` (und für einen Status `active` oder `instock`) aus, um sie im
Dropdown anzuzeigen. `getResolvedDocuments()` führt die Werte des Scopes in `_PRODUCT` zusammen:

```php
$suggest = new SuggestSearchStruct();
$suggest->setQuery('smart');
$suggest->setLocale('de');
$suggest->setAvailability('ch');

foreach ((new SuggestService($apiClient))->suggestWithFilter($suggest)->getResolvedDocuments() as $document) {
    echo $document['_PRODUCT']['price'] ?? '';
}
```

[Empfehlungen](recommendations) nehmen den Scope als letztes Argument und lösen ihn genauso auf.

{: .hinweis }
Filter und Sortierungen auf `_PRODUCT.price` oder `_PRODUCT.active` verwenden die Standardwerte. Filtere Werte eines
Scopes über ihren Knoten, zum Beispiel `_PRODUCT_availability.active`, sobald das Feld im Portal als filterbar
konfiguriert ist.
