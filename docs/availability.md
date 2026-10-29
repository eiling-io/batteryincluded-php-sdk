---
lang: en
permalink: /availability.html
title: Availability
nav_order: 4.5
---

# Availability
{: .no_toc }

1. TOC
{:toc}

Status, price and stock of a product can differ per country or sales channel. Like the translations, all scopes are
exported in one document and the gateway delivers only the requested one. The module works next to the
[translations](translations) and does not change them.

## Document schema

The default values stay in `_PRODUCT`, the scoped values are stored in `_PRODUCT_availability.<scope>`:

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

## Collection setup

Configure a second variable in the BatteryIncluded backend:

```json
{"name": "availability", "default": "de", "root": ["_PRODUCT_availability"]}
```

## Exporting availabilities

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

| Method | Description |
|---|---|
| `setAvailability($scope, active:, price:, instock:)` | Set several fields of a scope, `null` keeps the default |
| `addAvailability($scope, $field, $value)` | Set a single field of a scope |
| `getAvailability($scope)` / `getAvailabilities()` | Read the availabilities back |

- `ProductBaseDto` accepts the fields `active`, `price` and `instock`, other fields throw an `InvalidArgumentException`.
  Custom DTOs extend `AbstractAvailabilityDto` and override `getAvailabilityFields()`.
- Scopes are normalized like locales: `DE` becomes `de`, `b2b_DE` becomes `b2b-de`.
- `false` and `0` overwrite the default (inactive, out of stock), `null` and empty values do not.

## Requesting a scope

```php
$searchStruct = new BrowseSearchStruct();
$searchStruct->setLocale('de');
$searchStruct->setAvailability('ch');      // or $searchStruct->addVariable('availability', 'ch')
```

- Without `setAvailability()` the SDK sends no `v[availability]`, the `default` of the variable applies.
- There is no fallback between scopes: if a document lacks the requested scope, the default values apply.

## Reading hits

`BrowseResponse::getHits()` merges the translations first and then the availability into `_PRODUCT`, so the
availability wins for fields which are in both nodes:

```php
$result = (new BrowseService($apiClient))->browse($searchStruct);

foreach ($result->getHits() as $hit) {
    $product = $hit['document']['_PRODUCT'];
    echo $product['active'] ? $product['price'] : 'not available';
}

$result->getRawHits();      // untouched response including _PRODUCT_availability
$result->getAvailability(); // requested scope, null for the default
```

Use `AvailabilityResolver::resolveDocument($document, $scope)` to resolve documents from other sources the same way.

## Suggestions

`SuggestSearchStruct::setAvailability()` works the same way. The suggest endpoint delivers only the fields selected
under Suggest in the portal, select `price` (and `active` or `instock` for a status) to show them in the dropdown.
`getResolvedDocuments()` merges the values of the scope into `_PRODUCT`:

```php
$suggest = new SuggestSearchStruct();
$suggest->setQuery('smart');
$suggest->setLocale('de');
$suggest->setAvailability('ch');

foreach ((new SuggestService($apiClient))->suggestWithFilter($suggest)->getResolvedDocuments() as $document) {
    echo $document['_PRODUCT']['price'] ?? '';
}
```

[Recommendations](recommendations) take the scope as last argument and resolve it the same way.

{: .note }
Filters and sortings on `_PRODUCT.price` or `_PRODUCT.active` use the default values. Filter scoped values with their
node, e.g. `_PRODUCT_availability.active`, once the field is configured as filterable in the portal.
