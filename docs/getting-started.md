---
title: Getting started
nav_order: 2
---

# Getting started
{: .no_toc }

1. TOC
{:toc}

## Installation

Install the SDK with [Composer](https://getcomposer.org):

```bash
composer require batteryincluded/batteryincluded-php-sdk
```

The SDK has no runtime dependencies besides the `curl` and `mbstring` extensions. On PHP < 8.4 it ships a
polyfill for `mb_trim()`, which is loaded automatically by Composer.

## Creating the API client

Every service receives an `ApiClient`. It holds the base url, the collection and the API key and sends each request
with the `X-BI-API-KEY` header.

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

{: .warning }
The API key grants write access to your collection. Keep it on the server and read it from the environment or your
secret store, never ship it to the browser.

## Services at a glance

All services follow the same pattern: create them with the `ApiClient`, call a method, read the typed response.

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

| Service | Purpose |
|---|---|
| `Service\SyncService` | Import, update and delete documents |
| `Shop\BrowseService` | Search, filter, sort and paginate |
| `Suggest\SuggestService` | Search-as-you-type suggestions |
| `SimilarSearch\SimilarSearchService` | Related search queries |
| `Highlights\HighlightsService` | Popular searches and query suggestions |
| `Recommendations\RecommendationsService` | Recommendations for one product |
| `CartRecommendations\CartRecommendationsService` | Recommendations for a cart |
| `Presets\PresetsService` | Search presets configured in the backend |

## Responses

Every response extends `Service\Response`:

```php
$response->getStatusCode();  // http status code
$response->getBody();        // decoded json as array, [] for empty bodies and 404
$response->getRawResponse(); // the body as string
```

## Using your own HTTP client

`CurlHttpClient` is the default transport. To add logging, retries or proxies, implement
`HttpClientInterface` and pass it to the `ApiClient`:

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
