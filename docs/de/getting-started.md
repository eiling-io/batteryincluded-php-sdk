---
lang: de
permalink: /getting-started.html
title: Erste Schritte
nav_order: 2
---

# Erste Schritte
{: .no_toc }

1. TOC
{:toc}

## Installation {#installation}

Installiere das SDK mit [Composer](https://getcomposer.org):

```bash
composer require batteryincluded/batteryincluded-php-sdk
```

Das SDK hat außer den Extensions `curl` und `mbstring` keine Laufzeit-Abhängigkeiten. Unter PHP < 8.4 bringt es ein
Polyfill für `mb_trim()` mit, das Composer automatisch lädt.

## API-Client erstellen {#creating-the-api-client}

Jeder Service bekommt einen `ApiClient`. Er kennt die Basis-URL, die Collection und den API-Key und sendet jede Anfrage
mit dem Header `X-BI-API-KEY`.

```php
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    'customer.my-shop',   // collection
    'your-api-key'        // api key
);
```

{: .achtung }
Der API-Key erlaubt Schreibzugriff auf deine Collection. Behalte ihn auf dem Server und lies ihn aus der Umgebung oder
deinem Secret Store, gib ihn niemals an den Browser weiter.

## Die Services im Überblick {#services-at-a-glance}

Alle Services folgen demselben Muster: mit dem `ApiClient` erstellen, eine Methode aufrufen, die typisierte Antwort
auslesen.

```php
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;

$searchStruct = new BrowseSearchStruct();
$searchStruct->setQuery('smartphone');

$result = (new BrowseService($apiClient))->browse($searchStruct);

$result->getFound();     // number of hits
$result->getHits();      // documents of the current page
$result->getFacets();    // filters for the sidebar
```

| Service | Zweck |
|---|---|
| `Service\SyncService` | Dokumente importieren, aktualisieren und löschen |
| `Shop\BrowseService` | Suchen, filtern, sortieren und blättern |
| `Suggest\SuggestService` | Vorschläge während der Eingabe |
| `SimilarSearch\SimilarSearchService` | Verwandte Suchanfragen |
| `Highlights\HighlightsService` | Beliebte Suchen und Suchvorschläge |
| `Recommendations\RecommendationsService` | Empfehlungen zu einem Produkt |
| `CartRecommendations\CartRecommendationsService` | Empfehlungen zu einem Warenkorb |
| `Presets\PresetsService` | Im Backend konfigurierte Such-Presets |

## Antworten {#responses}

Jede Antwort erweitert `Service\Response`:

```php
$response->getStatusCode();  // http status code
$response->getBody();        // decoded json as array, [] for empty bodies and 404
$response->getRawResponse(); // the body as string
```

## Eigenen HTTP-Client verwenden {#using-your-own-http-client}

`CurlHttpClient` ist der Standard-Transport. Für Logging, Wiederholungen oder Proxys implementierst du
`HttpClientInterface` und übergibst ihn dem `ApiClient`:

```php
use BatteryIncludedSdk\Client\HttpClientInterface;
use BatteryIncludedSdk\Service\Response;

final class LoggingHttpClient implements HttpClientInterface
{
    public function __construct(private HttpClientInterface $inner, private LoggerInterface $logger)
    {
    }

    public function send(string $url, string $method, array $header, string $data): Response
    {
        $response = $this->inner->send($url, $method, $header, $data);
        $this->logger->info($method . ' ' . $url, ['status' => $response->getStatusCode()]);

        return $response;
    }
}

$apiClient = new ApiClient(new LoggingHttpClient(new CurlHttpClient(), $logger), $baseUrl, $collection, $apiKey);
```
