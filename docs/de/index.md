---
lang: de
permalink: /
title: Start
layout: home
nav_order: 1
---

# BatteryIncluded PHP SDK

Das offizielle PHP SDK für [BatteryIncluded](https://batteryincluded.ai). Es synchronisiert deine Produkte und Inhalte
in eine BatteryIncluded Collection und bietet dir typisierten Zugriff auf Suche, Vorschläge, Empfehlungen und mehr.

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

## Inhalt {#whats-inside}

| Thema | Klassen |
|---|---|
| [Erste Schritte](getting-started) | `ApiClient`, `CurlHttpClient`, `HttpClientInterface` |
| [Dokumente synchronisieren](syncing) | `ProductBaseDto`, `BlogBaseDto`, `SyncService` |
| [Übersetzungen (i18n)](translations) | `addTranslation()`, `I18nResolver` |
| [Verfügbarkeit](availability) | `setAvailability()`, `AvailabilityResolver` |
| [Backend-Konfiguration](backend-configuration) | Filterbare Felder, Filter, Suggest-Felder, Relevanz |
| [Suche & Filter](search) | `BrowseService`, `BrowseSearchStruct`, `BrowseResponse`, Facetten, Extensions |
| [Vorschläge](suggestions) | `SuggestService`, `SimilarSearchService`, `HighlightsService` |
| [Empfehlungen](recommendations) | `RecommendationsService`, `CartRecommendationsService`, `PresetsService` |
| [Demo-Shop](demo-shop) | Ein komplettes Shop-Frontend, gebaut mit dem SDK |
| [Entwicklung](development) | Tests, Code Style, statische Analyse |

## Für KI-Coding-Agenten {#for-ai-coding-agents}

Lass deinen Agenten [`llms-full.txt`](/llms-full.txt) lesen: Installation, Synchronisation, Übersetzungen,
Portal-Konfiguration, Suche, Vorschläge und eine Checkliste zur Prüfung in einer Datei. [`llms.txt`](/llms.txt) listet
alle Seiten dieser Dokumentation auf. Beide Dateien sind auf Englisch, damit kommen Agenten am besten zurecht.

## Voraussetzungen {#requirements}

- PHP 8.2 oder höher mit den Extensions `curl` und `mbstring`
- Eine BatteryIncluded Collection und ihr API-Key

Die [API-Referenz](https://www.postman.com/batteryincluded/core/overview) dokumentiert alle Endpunkte, mit denen das
SDK spricht.
