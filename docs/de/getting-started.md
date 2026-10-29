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
mit dem Header `X-BI-API-KEY` und einem `User-Agent` mit der Version von SDK und PHP.

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

### Timeouts {#timeouts}

`CurlHttpClient` bricht einen Lesezugriff (Suche, Vorschläge, Empfehlungen) nach 10 Sekunden und einen
Verbindungsaufbau nach 5 Sekunden ab. So blockiert eine langsame API deine Seiten nie bis zum PHP-Timeout. Importe,
Updates und Löschungen haben standardmäßig kein Limit, ein Import von einigen hundert Produkten mit langen
Beschreibungen kann Minuten dauern. Für das Rendern von Seiten passen kürzere Lese-Timeouts, `0` bedeutet kein Limit:

```php
$apiClient = new ApiClient(
    new CurlHttpClient(readTimeout: 2.0, connectTimeout: 1.0, writeTimeout: 600.0),
    $baseUrl, $collection, $apiKey
);
```

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
$response->isSuccessful();    // true for http 2xx
$response->getStatusCode();   // http status code
$response->getBody();         // decoded json as array, [] for errors and empty bodies
$response->getErrorMessage(); // message of an error response, null on success
$response->getRawResponse();  // the body as string
```

### Fehlerbehandlung {#error-handling}

Ein Fehler der API (4xx/5xx) wirft keine Exception. Die typisierten Antworten sind stattdessen leer (keine Treffer,
Facetten, Vorschläge oder Empfehlungen), so rendert deine Seite auch, wenn die Suche nicht erreichbar ist. Prüfe
`isSuccessful()`, um den Fehler zu loggen oder einen Hinweis anzuzeigen.

Exceptions gibt es nur, wenn die API nicht erreichbar ist oder ein Dokument nicht gesendet werden kann. Alle erweitern
`BatteryIncludedSdk\Exception\BatteryIncludedException`:

| Exception | Wann |
|---|---|
| `NetworkException` | Die API ist nicht erreichbar: DNS, Verbindung, TLS oder Timeout |
| `InvalidDocumentException` | Ein Dokument lässt sich nicht als JSON kodieren, zum Beispiel ein Preis `NAN`. Die Meldung nennt die Dokument-ID |

```php
use BatteryIncludedSdk\Exception\NetworkException;

try {
    $result = (new BrowseService($apiClient))->browse($searchStruct);
} catch (NetworkException $exception) {
    $result = null;   // api not reachable: dns, connection, tls or timeout
}

if ($result !== null && !$result->isSuccessful()) {
    $logger->warning('Search failed', ['status' => $result->getStatusCode(), 'error' => $result->getErrorMessage()]);
}
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
