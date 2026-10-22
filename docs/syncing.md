---
title: Syncing documents
nav_order: 3
---

# Syncing documents
{: .no_toc }

1. TOC
{:toc}

A collection holds documents of one or more types, e.g. products and blog posts. The SDK describes each document as
a DTO and sends them as NDJSON to the import endpoints.

## Products

```php
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;

$product = new ProductBaseDto('1002');          // identifier of the product in your shop
$product->setId('1002');
$product->setName('Ultra HD LED TV 65"');
$product->setDescription('4K television with HDR.');
$product->setOrdernumber('TV-65');
$product->setManufacture('BatteryIncluded');
$product->setManufactureNumber('BI-TV-65');
$product->setEan('4006381333931');
$product->setImageUrl('https://shop.example/images/tv-65.png');
$product->setShopUrl('https://shop.example/tv-65');
$product->setPrice(699.0);
$product->setInstock(14);
$product->setRating(4.5);

// a category is a path, every node becomes a level of the category tree
$product->addCategory((new CategoryDto())->addCategoryNode('Electronics')->addCategoryNode('TVs'));

// properties become filters, a property may have several values
$product->setProperties(
    (new ProductPropertyDto())
        ->addProperty('Color', 'Black')
        ->addProperty('Display size', '65')
);
```

The document is exported like this:

```json
{
  "id": "PRODUCT-1002",
  "type": "PRODUCT",
  "_PRODUCT": {
    "id": "1002",
    "name": "Ultra HD LED TV 65\"",
    "price": 699,
    "categories": ["Electronics", "Electronics > TVs"],
    "properties": [{"Color": ["Black"], "Display size": ["65"]}]
  }
}
```

- The document id is prefixed with its type (`PRODUCT-1002`), so products and blog posts never collide.
- All fields live in a type-scoped node (`_PRODUCT`). Filter and sort on them with that prefix, e.g. `_PRODUCT.price`.
- A category path is exported with all its parents (`Electronics`, `Electronics > TVs`).
- Fields that are `null` are not exported (see [Clearing fields](#clearing-fields)).

{: .note }
Keep property names stable and language independent (e.g. always `Color`). Labels are translated in the
BatteryIncluded backend and values via [translations](translations).

## Blog posts

```php
use BatteryIncludedSdk\Dto\BlogBaseDto;

$blog = new BlogBaseDto('1');
$blog->setId('1');
$blog->setTitle('Top 5 smartphones');
$blog->setAuthor('Jane Doe');
$blog->setPublishedAt('2026-06-01');
$blog->setActive(true);
$blog->setShortDescription('Our favourites of the year.');
$blog->setDescription('…');
$blog->setPreviewImage('https://shop.example/blog/top-5.png');
$blog->setBlogUrl('https://shop.example/blog/top-5');
```

Blog posts are stored as `BLOG-1` with their fields in `_BLOG`. Products and blog posts can be synced in one request
and searched together, see [filtering by type](search#filtering-by-type).

## Sync methods

```php
use BatteryIncludedSdk\Service\SyncService;

$syncService = new SyncService($apiClient);
```

| Method | Endpoint | Use it for |
|---|---|---|
| `syncOneOrManyElements(...$dtos)` | `POST /documents/import` | Create or replace the given documents |
| `syncFullElements(...$dtos)` | `POST /documents/import?full=1` | Full import, **documents not included are deleted** |
| `syncFullBatchElements($transactionId, $finished, ...$dtos)` | `POST /documents/import?transactionId=…` | Full import split into several requests |
| `partialUpdateOneOrManyElements(...$dtos)` | `PATCH /documents/update` | Update only the given fields |
| `deleteElementsByIds(...$ids)` | `DELETE /documents/delete` | Remove documents |

Each method returns a `Response` with one result per document:

```php
$result = $syncService->syncOneOrManyElements($product, $blog);
// [['id' => 'PRODUCT-1002', 'success' => true], ['id' => 'BLOG-1', 'success' => true]]
```

### Full import in batches

Large catalogs don't fit into a single request. Send them in chunks with the same transaction id and mark the last
request as finished. Only then does BatteryIncluded drop all documents that were not part of the transaction.

```php
$transactionId = 'full-import-' . date('YmdHis');
$chunks = array_chunk($products, 500);
$last = array_key_last($chunks);

foreach ($chunks as $index => $chunk) {
    $syncService->syncFullBatchElements($transactionId, $index === $last, ...$chunk);
}
```

### Partial updates

Send only the fields that changed, e.g. price and stock:

```php
$product = new ProductBaseDto('1002');
$product->setId('1002');
$product->setPrice(649.0);
$product->setInstock(9);

$syncService->partialUpdateOneOrManyElements($product);
```

### Clearing fields

Fields that are `null` are skipped on export, so a partial update never clears a field by accident. To explicitly send
`null` values, enable `exportNullValues()`:

```php
$product = new ProductBaseDto('1002');
$product->setId('1002');
$product->setRating(null);
$product->exportNullValues();

$syncService->partialUpdateOneOrManyElements($product);
```

### Deleting documents

```php
$syncService->deleteElementsByIds('240', '2');
```

## Custom fields

`ProductBaseDto` covers the standard fields. For shop-specific fields extend it and add them to the `_PRODUCT` node:

```php
use BatteryIncludedSdk\Dto\ProductBaseDto;

class ProductDto extends ProductBaseDto
{
    private ?string $keywords = null;

    public function setKeywords(?string $keywords): void
    {
        $this->keywords = $keywords;
    }

    public function jsonSerialize(): array
    {
        return array_merge_recursive(
            parent::jsonSerialize(),
            ['_' . $this->getType() => $this->filterJsonValues(['keywords' => $this->keywords])]
        );
    }
}
```

`filterJsonValues()` removes `null` values and respects `exportNullValues()`. A complete example is
[`examples/extension/product.php`](https://github.com/eiling-io/batteryincluded-php-sdk/blob/main/examples/extension/product.php).

## Custom document types

The type is the second constructor argument. Use it to store further content, e.g. stores or FAQ entries:

```php
$faq = new BlogBaseDto('42', 'FAQ'); // stored as FAQ-42 with the fields in _FAQ
```
