---
title: Home
layout: home
nav_order: 1
---

# BatteryIncluded PHP SDK

The official PHP SDK for [BatteryIncluded](https://batteryincluded.ai). It syncs your products and content to a
BatteryIncluded collection and gives you typed access to search, suggestions, recommendations and more.

```php
$apiClient = new ApiClient(new CurlHttpClient(), 'https://api.batteryincluded.io/api/v1/collections/', $collection, $apiKey);

// 1. sync a product
$product = new ProductBaseDto('1002');
$product->setName('Ultra HD LED TV 65"');
$product->setPrice(699);
(new SyncService($apiClient))->syncOneOrManyElements($product);

// 2. search for it
$searchStruct = new BrowseSearchStruct();
$searchStruct->setQuery('TV');
$result = (new BrowseService($apiClient))->browse($searchStruct);

foreach ($result->getHits() as $hit) {
    echo $hit['document']['_PRODUCT']['name'];
}
```

## What's inside

| Topic | Classes |
|---|---|
| [Getting started](getting-started) | `ApiClient`, `CurlHttpClient`, `HttpClientInterface` |
| [Syncing documents](syncing) | `ProductBaseDto`, `BlogBaseDto`, `SyncService` |
| [Translations (i18n)](translations) | `addTranslation()`, `I18nResolver` |
| [Backend configuration](backend-configuration) | Filterable fields, filters, suggest fields, relevance |
| [Search & filters](search) | `BrowseService`, `BrowseSearchStruct`, `BrowseResponse`, facets, extensions |
| [Suggestions](suggestions) | `SuggestService`, `SimilarSearchService`, `HighlightsService` |
| [Recommendations](recommendations) | `RecommendationsService`, `CartRecommendationsService`, `PresetsService` |
| [Demo shop](demo-shop) | A complete storefront built with the SDK |
| [Development](development) | Tests, code style, static analysis |

## Requirements

- PHP 8.2 or higher with the `curl` and `mbstring` extensions
- A BatteryIncluded collection and its API key

The [API reference](https://www.postman.com/batteryincluded/core/overview) documents all endpoints the SDK talks to.
