---
lang: en
permalink: /demo-shop.html
title: Demo shop
nav_order: 9
---

# Demo shop
{: .no_toc }

1. TOC
{:toc}

The `examples` directory contains a complete storefront built with the SDK. Use it as a reference implementation or
to try changes against your own collection.

## Features

- Search with suggestions as you type (`examples/api/suggest.php`)
- Facets in the sidebar: category tree, select, range and rating filters, plus a type filter for products and blog posts
- Sorting, pagination, teasers, promotions, redirects and promotion codes
- Language switch (German / English) with [translated documents](translations)
- Country switch (Germany / Austria / Switzerland) with [prices and status per country](availability), products which are not sold in a country are greyed out
- Detail page with all fields of a document, their source (translation or default) and the raw api response
- Light and dark mode

## Running it

The repository ships a [DDEV](https://ddev.com) configuration with `examples/` as document root:

```bash
ddev start
ddev composer install
```

Create `examples/credentials.php` (it is ignored by git) with your collection and API key:

```php
<?php

$collection = 'customer.my-shop';   // keep the prefix "customer."
$apiKey = 'your-api-key';
```

Then open `https://batteryincluded-php-sdk.ddev.site`.

Without DDEV the built-in PHP server works as well:

```bash
composer install
php -S 127.0.0.1:8000 -t examples
```

## Test data

`examples/sync/sync_full_product_and_blogs.php` fills the collection with the test data of the test suite:
720 products (smartphones, tablets and laptops in four colours and three storage sizes, with German and English
translations and generated SVG images) and 10 blog posts.

{: .warning }
The script runs a full import, all other documents of the collection are deleted.

## Structure

| File | Content |
|---|---|
| `examples/index.php` | Search result page |
| `examples/detail.php` | Detail page of a product or blog post |
| `examples/api/suggest.php` | JSON endpoint for the suggestions |
| `examples/app/bootstrap.php` | Configuration and helpers |
| `examples/app/translations.php` | UI texts per language |
| `examples/app/partials/` | Shared header and footer |
| `examples/assets/` | Styles and scripts |

Single-purpose examples for every service live next to it, e.g. `examples/sync/`, `examples/shop/` and
`examples/recommendations/`.
