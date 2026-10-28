---
lang: en
permalink: /translations.html
title: Translations (i18n)
nav_order: 4
---

# Translations (i18n)
{: .no_toc }

1. TOC
{:toc}

A document can carry its language dependent fields for several locales. The gateway delivers only the requested
locale, so one collection serves all languages of your shop.

## Document schema

Language dependent fields are stored in `_<TYPE>_i18n.<locale>`, everything else in `_<TYPE>`:

```json
{
  "id": "PRODUCT-1002",
  "type": "PRODUCT",
  "_PRODUCT_i18n": {
    "de": {"name": "Ultra HD LED-TV 65\"", "categories": ["Elektronik", "Elektronik > Fernseher"]},
    "en": {"name": "Ultra HD LED TV 65\"", "categories": ["Electronics", "Electronics > TVs"]}
  },
  "_PRODUCT": {
    "name": "Ultra HD LED-TV 65\"",
    "price": 699,
    "instock": 14
  }
}
```

Blog posts use `_BLOG_i18n` accordingly.

## Collection setup

The collection needs a variable that tells the gateway where the locales are located. Configure it once in the
BatteryIncluded backend:

```json
{"name": "locale", "default": "de", "root": ["_PRODUCT_i18n", "_BLOG_i18n"]}
```

Variables are generic: the same mechanism works for other names and roots, e.g. `currency` or `webshop`.

## Exporting translations

```php
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;

$product = new ProductBaseDto('1002');
$product->setName('Ultra HD LED-TV 65"');   // default value
$product->setPrice(699);

$product
    ->addTranslation('de', 'name', 'Ultra HD LED-TV 65"')
    ->addCategoryTranslation('de', (new CategoryDto())->addCategoryNode('Elektronik')->addCategoryNode('Fernseher'))
    ->addPropertyTranslation('de', 'Color', 'Schwarz');

$product
    ->addTranslation('en', 'name', 'Ultra HD LED TV 65"')
    ->addTranslation('en', 'shopUrl', 'https://shop.example/en/tv-65')
    ->addCategoryTranslation('en', (new CategoryDto())->addCategoryNode('Electronics')->addCategoryNode('TVs'))
    ->setPropertiesTranslation('en', (new ProductPropertyDto())->addProperty('Color', 'Black'));
```

| Method | Description |
|---|---|
| `addTranslation($locale, $field, $value)` | Translate a single field |
| `addCategoryTranslation($locale, CategoryDto)` | Add a translated category path |
| `addPropertyTranslation($locale, $option, $value)` | Add a translated property value |
| `setPropertiesTranslation($locale, ProductPropertyDto)` | Replace all translated properties of a locale |
| `getTranslation($locale)` / `getTranslations()` | Read the translations back |

- Export every locale, including the language of your regular fields (e.g. `de`).
- Locales are normalized: `DE` becomes `de`, `de_CH` becomes `de-ch`.
- Only translatable fields are accepted, a typo like `nmae` throws an `InvalidArgumentException`.
- Keep the regular fields in `_PRODUCT` filled. They act as the default whenever a locale is missing.
- Property names stay the same in every locale, only the values are translated.

## Requesting a locale

Set the locale on the search struct, the SDK sends it as `v[locale]`:

```php
$searchStruct = new BrowseSearchStruct();
$searchStruct->setLocale('en');            // or $searchStruct->addVariable('locale', 'en')
```

`SuggestSearchStruct::setLocale()` and `SimilarSearchService::search($query, $locale)` work the same way.

The gateway resolves the node for the requested locale:

```json
{
  "_PRODUCT_i18n": {"name": "Ultra HD LED TV 65\"", "categories": ["Electronics", "Electronics > TVs"]},
  "_PRODUCT": {"name": "Ultra HD LED-TV 65\"", "price": 699, "instock": 14}
}
```

- Without `v[locale]` the `default` of the variable applies.
- There is no fallback in the gateway: if a document lacks the requested locale, the node is removed.

{: .warning }
Always send a locale to the suggest endpoint. Without it the gateway currently fails with a 500 as soon as the
collection uses translated facets. The SDK sends `DE` by default.

## Reading translated hits

`BrowseResponse::getHits()` merges `_PRODUCT_i18n` into `_PRODUCT` and removes the node. Your templates read
`_PRODUCT.name` and get the translated value, or the default if no translation exists:

```php
$result = (new BrowseService($apiClient))->browse($searchStruct);

foreach ($result->getHits() as $hit) {
    echo $hit['document']['_PRODUCT']['name']; // "Ultra HD LED TV 65""
}

$result->getRawHits(); // untouched response including _PRODUCT_i18n
$result->getLocale();  // locale the hits were resolved for
```

Use `I18nResolver::resolveDocument($document, $locale)` to resolve documents from other sources the same way.

## Filtering and sorting

Filter translated fields without the locale, the gateway adds it internally:

```php
$searchStruct->setLocale('en');
$searchStruct->addFilter('_PRODUCT_i18n.categories', 'Electronics > TVs');
$searchStruct->addFilter('_PRODUCT_i18n.properties.Color', 'Black');
```

The facets of the requested locale are named the same way (`_PRODUCT_i18n.properties.Color`). The gateway also
delivers the facets of the other locales (`_PRODUCT_i18n.de.properties.Color`), hide them in your UI.
