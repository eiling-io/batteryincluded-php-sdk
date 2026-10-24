---
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

foreach ($response->getDocuments() as $document) {
    echo $document['_PRODUCT_i18n']['name'] ?? $document['_PRODUCT']['name'];  // highlighted name
}

$response->getFounds();   // number of matching documents
$response->getFacets();   // facets configured for suggestions
$response->isLLM();       // true if the api flagged the response with `llm`
```

- Documents contain the highlighted fields selected under [Suggest in the backend](backend-configuration#suggest). Add
  `id` there if you want to link to detail pages.
- Translated fields stay in `_PRODUCT_i18n`, there is no merging like in `BrowseResponse`.
- Always set a locale, see [translations](translations#requesting-a-locale).
- `setAvailability('ch')` sends `v[availability]` like on the search page. Prices only arrive if `price` is selected
  under Suggest in the portal, see [availability](availability#suggestions).

`suggest(string $query, string $locale = 'DE')` is a shortcut without filters. It is deprecated in favour of
`suggestWithFilter()`.

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

$highlights = (new HighlightsService($apiClient))->getHighlights();

$highlights->getSearches();
$highlights->getQuerySuggestions();
$highlights->getAll();   // ['searches' => [...], 'querySuggestions' => [...]]
```
