---
title: Search & filters
nav_order: 6
---

# Search & filters
{: .no_toc }

1. TOC
{:toc}

`BrowseService` powers search result pages and category listings: full text search, filters, sorting, pagination,
facets and merchandising extensions in one request.

## Basic search

```php
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;

$searchStruct = new BrowseSearchStruct();
$searchStruct->setQuery('smartphone');
$searchStruct->setLocale('en');
$searchStruct->setPerPage(24);   // default 10
$searchStruct->setPage(2);       // default 1

$result = (new BrowseService($apiClient))->browse($searchStruct);

$result->getFound();  // total number of hits
$result->getPage();   // current page
$result->getPages();  // number of pages
```

Without a query (or with `*`) all documents are returned, which is what you want for category pages.

## Reading hits

```php
foreach ($result->getHits() as $hit) {
    $document = $hit['document'];

    match ($document['type']) {
        'PRODUCT' => printf("%s – %.2f €\n", $document['_PRODUCT']['name'], $document['_PRODUCT']['price']),
        'BLOG' => printf("%s by %s\n", $document['_BLOG']['title'], $document['_BLOG']['author']),
    };
}
```

Each hit also contains `highlight` with the matched tokens of the query. See [translations](translations#reading-translated-hits)
for how `getHits()` and `getRawHits()` differ.

## Filters

```php
// several values of one field are combined with OR
$searchStruct->addFilter('_PRODUCT.properties.Color', 'Black');
$searchStruct->addFilter('_PRODUCT.properties.Color', 'Blue');

// different fields are combined with AND
$searchStruct->addFilter('_PRODUCT.properties.Storage', '512GB');

// a category filter includes all subcategories
$searchStruct->addFilter('_PRODUCT.categories', 'Electronics > TVs');

// ranges
$searchStruct->addRangeFilter('_PRODUCT.price', '100', '500');

// filters from a request, e.g. ?f[_PRODUCT.properties.Color][]=Black&f[_PRODUCT.price][from]=100
$searchStruct->addFilters($_GET['f'] ?? []);
```

### Filtering by type

A collection can mix products and blog posts. Restrict the result with the `type` filter:

```php
$searchStruct->addFilter('type', 'PRODUCT');
```

### Fetching a single document

Filter by the document id to load one document, e.g. for a detail page:

```php
$searchStruct = new BrowseSearchStruct();
$searchStruct->addFilter('id', 'PRODUCT-1002');
$searchStruct->setPerPage(1);

$document = (new BrowseService($apiClient))->browse($searchStruct)->getHits()[0]['document'] ?? null;
```

## Sorting

```php
$searchStruct->setSort('_PRODUCT.price:asc');
$searchStruct->setSort('_PRODUCT.rating:desc');
```

Without sort the result is ordered by relevance.

## Presets and variables

A preset bundles search settings that are maintained in the backend (e.g. ranking rules for a landing page):

```php
$searchStruct->setPresetId('857e117c-3766-494d-a692-d7a23c384c33');
```

List the available presets with the [`PresetsService`](recommendations#presets).

Variables are passed to the gateway as `v[name]`. `locale` is set by `setLocale()`, others are defined in your
collection:

```php
$searchStruct->addVariable('currency', 'EUR');
```

## Facets

Facets exist only for fields that are [configured as filters in the backend](backend-configuration#filters).
`getFacets()` turns the facet counts into typed objects. Pass the field that holds your category tree, e.g.
`_PRODUCT_i18n.categories` for [translated categories](translations#filtering-and-sorting):

```php
use BatteryIncludedSdk\Shop\FacetCategoryDto;
use BatteryIncludedSdk\Shop\FacetRangeDto;
use BatteryIncludedSdk\Shop\FacetRatingDto;
use BatteryIncludedSdk\Shop\FacetSelectDto;

foreach ($result->getFacets('_PRODUCT.categories') as $facet) {
    echo $facet->getFieldLabel();   // label from the backend, e.g. "Color"
    $facet->getFieldName();         // filter key, e.g. "_PRODUCT.properties.Color"
    $facet->getFieldUnit();         // e.g. "€"

    if ($facet instanceof FacetSelectDto) {
        foreach ($facet->getValues() as $value) {
            printf("[%s] %s (%d)\n", $value->isChecked() ? 'x' : ' ', $value->getName(), $value->getCount());
        }
    } elseif ($facet instanceof FacetRangeDto) {
        printf("%s – %s, selected %s – %s\n", $facet->getMin(), $facet->getMax(), $facet->getSelectedMin(), $facet->getSelectedMax());
    } elseif ($facet instanceof FacetRatingDto) {
        $facet->getRatings();       // FacetValueDto[] per star rating
    } elseif ($facet instanceof FacetCategoryDto) {
        $facet->getCategories();    // nested tree: ['Electronics' => ['count' => 12, 'checked' => true, 'childs' => [...]]]
    }
}
```

The `checked` state reflects the filters of the search struct, so you can render the sidebar directly from the
response.

## Extensions

Merchandising rules configured in the backend are delivered as extensions:

| Method | Content |
|---|---|
| `getTeaserExtensions()` | Banners to place between the hits (`position`, `image`, `url`) |
| `getPromotionsExtensions()` | Banners above the result |
| `getRedirectsExtensions()` | A url to redirect to, e.g. for the query "contact" |
| `getCodesExtensions()` | A promotion code that matched the query |
| `getAllExtensions()` | All of the above |

```php
foreach ($result->getRedirectsExtensions() as $redirect) {
    header('Location: ' . $redirect->getData()['url']);
    exit;
}
```
