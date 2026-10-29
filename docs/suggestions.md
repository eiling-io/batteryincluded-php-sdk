---
lang: en
permalink: /suggestions.html
title: Suggestions
nav_order: 7
---

# Suggestions
{: .no_toc }

1. TOC
{:toc}

## Search as you type

`SuggestService` returns query completions and matching documents while the visitor is typing.

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

foreach ($response->getResolvedDocuments() as $document) {
    echo $document['_PRODUCT']['name'];  // highlighted name in the requested locale
}

$response->getFounds();   // number of matching documents
$response->getFacets();   // facets configured for suggestions
$response->isLLM();       // true if the api flagged the response with `llm`
```

- Documents contain the highlighted fields selected under [Suggest in the backend](backend-configuration#suggest). Add
  `id` (the document id in the root, e.g. `PRODUCT-1002`) there if you want to link to detail pages.
- `getResolvedDocuments()` merges the translations and the availability into `_PRODUCT`, like
  `BrowseResponse::getHits()`. `getDocuments()` returns the documents as delivered, including `_PRODUCT_i18n`.
- Always set a locale, see [translations](translations#requesting-a-locale).
- `setAvailability('ch')` sends `v[availability]` like on the search page. Prices only arrive if `price` is selected
  under Suggest in the portal, see [availability](availability#suggestions).

{: .note }
Escape everything except the `<mark>` tags before you output highlighted values.

## Similar searches

Related queries other visitors searched for, e.g. for "did you mean" or "others also searched":

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

## Highlights

Popular searches and query suggestions, e.g. to fill an empty search layer:

```php
use BatteryIncludedSdk\Highlights\HighlightsService;

$highlights = (new HighlightsService($apiClient))->getHighlights('de');   // locale of the query suggestions

$highlights->getSearches();
$highlights->getQuerySuggestions();
$highlights->getAll();   // ['searches' => [...], 'querySuggestions' => [...]]
```

With [translations](translations) the query suggestions are configured per locale, pass the locale of the shop. Without
locale the gateway delivers no query suggestions of a translated collection.
