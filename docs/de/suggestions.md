---
lang: de
permalink: /suggestions.html
title: Vorschläge
nav_order: 7
---

# Vorschläge
{: .no_toc }

1. TOC
{:toc}

## Suche während der Eingabe {#search-as-you-type}

`SuggestService` liefert Vervollständigungen und passende Dokumente, während der Besucher tippt.

```php
use BatteryIncludedSdk\Suggest\SuggestSearchStruct;
use BatteryIncludedSdk\Suggest\SuggestService;

$searchStruct = new SuggestSearchStruct();
$searchStruct->setQuery('smart');
$searchStruct->setLocale('en');
$searchStruct->addFilter('type', 'PRODUCT');

$response = (new SuggestService($apiClient))->suggestWithFilter($searchStruct);

foreach ($response->getQueryCompletions() as $completion) {
    echo $completion->getHighlighted();   // "<mark>smart</mark>phone"
    $completion->getValue();              // "smartphone"
    $completion->getScore();
    $completion->getSource();
}

foreach ($response->getDocuments() as $document) {
    echo $document['_PRODUCT_i18n']['name'] ?? $document['_PRODUCT']['name'];  // highlighted name
}

$response->getFounds();   // number of matching documents
$response->getFacets();   // facets configured for suggestions
$response->isLLM();       // true if the api flagged the response with `llm`
```

- Die Dokumente enthalten die hervorgehobenen Felder, die [im Backend unter Suggest](backend-configuration#suggest)
  ausgewählt sind. Füge dort `id` hinzu (die Dokument-ID im Root, zum Beispiel `PRODUCT-1002`), wenn du auf
  Detailseiten verlinken willst.
- Übersetzte Felder bleiben in `_PRODUCT_i18n`, anders als bei `BrowseResponse` wird nichts zusammengeführt.
- Setze immer eine Locale, siehe [Übersetzungen](translations#requesting-a-locale).
- `setAvailability('ch')` sendet `v[availability]` wie auf der Suchseite. Preise kommen nur an, wenn `price` im Portal
  unter Suggest ausgewählt ist, siehe [Verfügbarkeit](availability#suggestions).

`suggest(string $query, string $locale = 'DE')` ist eine Abkürzung ohne Filter. Sie ist zugunsten von
`suggestWithFilter()` deprecated.

{: .hinweis }
Escape alles außer den `<mark>`-Tags, bevor du hervorgehobene Werte ausgibst.

## Ähnliche Suchen {#similar-searches}

Verwandte Suchanfragen anderer Besucher, zum Beispiel für „Meinten Sie“ oder „Andere suchten auch“:

```php
use BatteryIncludedSdk\SimilarSearch\SimilarSearchService;

$response = (new SimilarSearchService($apiClient))->search('smartphone', 'en');

foreach ($response->getSimilarSearches() as $similar) {
    $similar->getQuery();        // "smartphone case"
    $similar->getHighlighted();
    $similar->getCount();
    $similar->getHits();
}
```

## Highlights {#highlights}

Beliebte Suchen und Suchvorschläge, zum Beispiel um eine leere Suchebene zu füllen:

```php
use BatteryIncludedSdk\Highlights\HighlightsService;

$highlights = (new HighlightsService($apiClient))->getHighlights('de');   // locale of the query suggestions

$highlights->getSearches();
$highlights->getQuerySuggestions();
$highlights->getAll();   // ['searches' => [...], 'querySuggestions' => [...]]
```

Mit [Übersetzungen](translations) werden die Suchvorschläge pro Locale konfiguriert, übergib die Locale des Shops. Ohne
Locale liefert das Gateway bei einer übersetzten Collection keine Suchvorschläge.
