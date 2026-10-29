---
lang: de
permalink: /translations.html
title: Übersetzungen (i18n)
nav_order: 4
---

# Übersetzungen (i18n)
{: .no_toc }

1. TOC
{:toc}

Ein Dokument kann seine sprachabhängigen Felder für mehrere Locales enthalten. Das Gateway liefert nur die angefragte
Locale aus, so bedient eine Collection alle Sprachen deines Shops.

## Dokumentschema {#document-schema}

Sprachabhängige Felder werden in `_<TYPE>_i18n.<locale>` gespeichert, alles andere in `_<TYPE>`:

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

Blogbeiträge verwenden entsprechend `_BLOG_i18n`.

## Collection einrichten {#collection-setup}

Die Collection braucht eine Variable, die dem Gateway sagt, wo die Locales liegen. Lege sie einmalig im BatteryIncluded
Backend an:

```json
{"name": "locale", "default": "de", "root": ["_PRODUCT_i18n", "_BLOG_i18n"]}
```

Variablen sind generisch: Derselbe Mechanismus funktioniert auch für andere Namen und Knoten, zum Beispiel `currency`
oder `webshop`.

## Übersetzungen exportieren {#exporting-translations}

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

| Methode | Beschreibung |
|---|---|
| `addTranslation($locale, $field, $value)` | Ein einzelnes Feld übersetzen |
| `addCategoryTranslation($locale, CategoryDto)` | Einen übersetzten Kategoriepfad hinzufügen |
| `addPropertyTranslation($locale, $option, $value)` | Einen übersetzten Eigenschaftswert hinzufügen |
| `setPropertiesTranslation($locale, ProductPropertyDto)` | Alle übersetzten Eigenschaften einer Locale ersetzen |
| `getTranslation($locale)` / `getTranslations()` | Die Übersetzungen wieder auslesen |

- Exportiere jede Locale, auch die Sprache deiner regulären Felder (zum Beispiel `de`).
- Locales werden normalisiert: Aus `DE` wird `de`, aus `de_CH` wird `de-ch`.
- Nur übersetzbare Felder sind erlaubt, ein Tippfehler wie `nmae` wirft eine `InvalidArgumentException`.
- Lass die regulären Felder in `_PRODUCT` befüllt. Sie dienen als Standardwert, wenn eine Locale fehlt.
- Eigenschaftsnamen bleiben in jeder Locale gleich, übersetzt werden nur die Werte.

## Eine Locale anfragen {#requesting-a-locale}

Setze die Locale im Search Struct, das SDK sendet sie als `v[locale]`:

```php
$searchStruct = new BrowseSearchStruct();
$searchStruct->setLocale('en');            // or $searchStruct->addVariable('locale', 'en')
```

`SuggestSearchStruct::setLocale()`, `SimilarSearchService::search($query, $locale)`,
`HighlightsService::getHighlights($locale)` und das optionale `$locale` der [Empfehlungen](recommendations)
funktionieren genauso.

Das Gateway löst den Knoten für die angefragte Locale auf:

```json
{
  "_PRODUCT_i18n": {"name": "Ultra HD LED TV 65\"", "categories": ["Electronics", "Electronics > TVs"]},
  "_PRODUCT": {"name": "Ultra HD LED-TV 65\"", "price": 699, "instock": 14}
}
```

- Ohne `v[locale]` gilt der `default` der Variable.
- Das Gateway hat keinen Fallback: Fehlt einem Dokument die angefragte Locale, wird der Knoten entfernt.
- Frage genau die exportierten Locales an: `de` findet keine Dokumente, die mit `de-de` exportiert wurden, und umgekehrt.

{: .achtung }
Sende an den Suggest-Endpunkt immer eine Locale. Ohne sie bricht das Gateway derzeit mit einem 500 ab, sobald die
Collection übersetzte Facetten verwendet. Die Search Structs und `SimilarSearchService` senden standardmäßig `DE`
(normalisiert `de`), das passt nur zu Collections, die mit `de` exportiert wurden. Setze die Locale deines Shops, zum
Beispiel `de_DE` für Dokumente, die mit `de-de` exportiert wurden.

## Übersetzte Treffer auslesen {#reading-translated-hits}

`BrowseResponse::getHits()` führt `_PRODUCT_i18n` in `_PRODUCT` zusammen und entfernt den Knoten. Deine Templates lesen
`_PRODUCT.name` und bekommen den übersetzten Wert oder, falls es keine Übersetzung gibt, den Standardwert:

```php
$result = (new BrowseService($apiClient))->browse($searchStruct);

foreach ($result->getHits() as $hit) {
    echo $hit['document']['_PRODUCT']['name']; // "Ultra HD LED TV 65""
}

$result->getRawHits(); // untouched response including _PRODUCT_i18n
$result->getLocale();  // locale the hits were resolved for
```

`SuggestResponse::getResolvedDocuments()` und die [Empfehlungen](recommendations) mit Locale führen genauso zusammen. Mit
`I18nResolver::resolveDocument($document, $locale)` löst du Dokumente aus anderen Quellen auf.

## Filtern und sortieren {#filtering-and-sorting}

Filtere übersetzte Felder ohne die Locale, das Gateway ergänzt sie intern:

```php
$searchStruct->setLocale('en');
$searchStruct->addFilter('_PRODUCT_i18n.categories', 'Electronics > TVs');
$searchStruct->addFilter('_PRODUCT_i18n.properties.Color', 'Black');
```

Die Facetten der angefragten Locale heißen genauso (`_PRODUCT_i18n.properties.Color`). Das Gateway liefert außerdem die
Facetten der anderen Locales aus (`_PRODUCT_i18n.de.properties.Color`), `getFacets()` überspringt sie.
