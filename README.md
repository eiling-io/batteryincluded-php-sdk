![Alt](https://repobeats.axiom.co/api/embed/96ce2691516d9a6a2821e3b67c5280059efe89b2.svg "Repobeats analytics image")[![Discord](https://img.shields.io/badge/Discord-Join%20Server-7289DA?style=for-the-badge&logo=discord&logoColor=white)](https://discord.gg/fAbqNjwG)

[![Latest Stable Version](https://poser.pugx.org/batteryincluded/batteryincluded-php-sdk/v/stable)](https://packagist.org/packages/batteryincluded/batteryincluded-php-sdk) [![Packagist](https://img.shields.io/packagist/dt/batteryincluded/batteryincluded-php-sdk.svg)](https://packagist.org/packages/batteryincluded/batteryincluded-php-sdk)

# batteryincluded-php-sdk
The SDK is licensed under the [MIT License](LICENSE). Feel free to contribute!

## Using the SDK

📖 **[Read the documentation](https://eiling-io.github.io/batteryincluded-php-sdk/)** for guides on syncing, search, translations, suggestions and recommendations.

The [API documentation](https://www.postman.com/batteryincluded/core/overview) provides all information about the available endpoints.

### Install & Integrate the SDK into your Project

The SDK requires a PHP version of 8.2 or higher. The recommended way to install the SDK is through [Composer](http://getcomposer.org).

```bash
composer require batteryincluded/batteryincluded-php-sdk
```

### Usage
You can find for every implemented api action an example file in the examples directory.

### Extending ProductBaseDto with Custom Fields

`ProductBaseDto` covers the standard product fields (`name`, `description`, `ordernumber`, `price`, `instock`, `rating`, etc.). To sync additional, shop-specific fields (e.g. `keywords`, `material`, `color`), extend the class and override `jsonSerialize()`.

**1. Create a subclass**

```php
use BatteryIncludedSdk\Dto\ProductBaseDto;

class ProductDto extends ProductBaseDto
{
    private ?string $keywords = null;

    public function setKeywords(string $keywords): void
    {
        $this->keywords = $keywords;
    }

    private function getKeywords(): ?string
    {
        return $this->keywords;
    }

    public function jsonSerialize(): array
    {
        $jsonDto = [
            'keywords' => $this->getKeywords(),
        ];

        return array_merge_recursive(
            parent::jsonSerialize(),
            ['_' . $this->getType() => array_filter($jsonDto, static fn($value) => $value !== null)]
        );
    }
}
```

The custom fields must be nested under the `_PRODUCT` key (i.e. `'_' . $this->getType()`). `array_merge_recursive` merges them into the parent payload without overwriting the base fields. `array_filter` strips `null` values so only populated fields are sent.

**2. Populate and sync**

```php
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Service\SyncService;

$product = new ProductDto('1');
$product->setName('iPhone 15 Pro');
$product->setOrdernumber('AP-001-128GB');
$product->setPrice(1199.00);
$product->setInstock(42);
$product->setKeywords('smartphone apple ios');

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$syncService = new SyncService($apiClient);
$result = $syncService->syncFullElements($product);
```

Pass multiple products as separate arguments to `syncFullElements()` to sync them in a single request. A full working example is available in [`examples/extension/product.php`](examples/extension/product.php).

### Mixed Index: Products and Blog Posts in a Single Collection

A single collection can hold multiple document types. Each document carries a `type` field and a type-scoped data key (e.g. `_PRODUCT`, `_BLOG`), so the index stores heterogeneous content while still allowing type-specific searches.

**1. Sync products and blogs together**

```php
use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\BlogBaseDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Service\SyncService;

$product = new ProductBaseDto('1');
$product->setName('iPhone 15 Pro');
$product->setPrice(1199.00);

$blog = new BlogBaseDto('1');
$blog->setTitle('Top 5 Smartphones 2024');
$blog->setAuthor('Jane Doe');
$blog->setPublishedAt('2024-06-01');

$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$syncService = new SyncService($apiClient);
$syncService->syncFullElements($product, $blog);
```

Each document is stored with a prefixed ID (`PRODUCT-1`, `BLOG-1`) and a `type` field, so documents from different types never collide even when their identifiers overlap.

**2. Search all types at once**

```php
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;

$searchStruct = new BrowseSearchStruct();
$searchStruct->setQuery('iPhone');

$browseService = new BrowseService($apiClient);
$result = $browseService->browse($searchStruct); // returns products AND blogs
```

**3. Filter to a specific type**

Pass `type` as a filter key to restrict results to only products or only blog posts:

```php
// Only products
$searchStruct = new BrowseSearchStruct();
$searchStruct->setQuery('iPhone');
$searchStruct->addFilter('type', 'PRODUCT');

// Only blog posts
$searchStruct = new BrowseSearchStruct();
$searchStruct->setQuery('iPhone');
$searchStruct->addFilter('type', 'BLOG');
```

**4. Access type-specific fields in the result**

Each hit contains the type-scoped key, so check `type` first to access the right payload:

```php
foreach ($result->getHits() as $hit) {
    $document = $hit['document'];

    if ($document['type'] === 'PRODUCT') {
        $data = $document['_PRODUCT'];
        echo $data['name'] . ' – ' . $data['price'] . ' €';
    } elseif ($document['type'] === 'BLOG') {
        $data = $document['_BLOG'];
        echo $data['title'] . ' by ' . $data['author'];
    }
}
```

A full working example is available in [`examples/sync/sync_full_product_and_blogs.php`](examples/sync/sync_full_product_and_blogs.php).

### Translations (i18n)

Language dependent fields are exported in the root node `_<TYPE>_i18n`, scoped by locale (`_PRODUCT_i18n.<locale>`, `_BLOG_i18n.<locale>`, ...), everything else stays in `_PRODUCT` / `_BLOG`.
Export every locale there, including the language of the regular fields (e.g. `de`). Locales are normalized to lower case (`DE` => `de`, `de_CH` => `de-ch`).
Property names are always english in every locale, only the values are translated (the labels are translated in the BatteryIncluded backend):

```php
$product = new ProductBaseDto('1002');
$product->setName('Ultra HD HDR LED-TV 65"');
$product->setPrice(699);

$product->addTranslation('de', 'name', 'Ultra HD HDR LED-TV 65"');
$product->addCategoryTranslation('de', (new CategoryDto())->addCategoryNode('Elektronik')->addCategoryNode('Fernseher'));

$product->addTranslation('en', 'name', 'Ultra HD HDR LED TV 65"');
$product->addCategoryTranslation('en', (new CategoryDto())->addCategoryNode('Electronics')->addCategoryNode('TVs'));
$product->addPropertyTranslation('en', 'Color', 'Black');

$blog = new BlogBaseDto('1');
$blog->setTitle('Neuigkeiten');
$blog->addTranslation('en', 'title', 'News');
```

```json
{"id": "PRODUCT-1002", "type": "PRODUCT",
  "_PRODUCT": {"name": "Ultra HD HDR LED-TV 65\"", "price": 699},
  "_PRODUCT_i18n": {
    "de": {"name": "Ultra HD HDR LED-TV 65\"", "categories": ["Elektronik", "Elektronik > Fernseher"]},
    "en": {"name": "Ultra HD HDR LED TV 65\"", "categories": ["Electronics", "Electronics > TVs"], "properties": [{"Color": ["Black"]}]}}}
```

The collection needs a variable which tells the gateway where the locales are located:

```json
{"name": "locale", "default": "de", "root": ["_PRODUCT_i18n", "_BLOG_i18n"]}
```

The locale is sent as `v[locale]` (`BrowseSearchStruct::setLocale()` or the `locale` variable). The gateway resolves the node for the requested locale (`_PRODUCT_i18n.en` becomes `_PRODUCT_i18n`), without `v[locale]` the default of the variable is used. There is no fallback in the gateway: if the requested locale is missing in a document, the node is removed.

`BrowseResponse::getHits()` merges `_PRODUCT_i18n` into `_PRODUCT` (and `_BLOG_i18n` into `_BLOG`) and removes the node, the regular values of `_PRODUCT` remain as fallback for missing translations. Use `getRawHits()` to access the untouched response.

Translated values are filtered and sorted without the locale, the gateway adds it internally, e.g. `_PRODUCT_i18n.categories` or `_PRODUCT_i18n.properties.Color`.

## Community

Join our community on [Discord](https://discord.gg/fAbqNjwG) to ask questions, give feedback, or connect with other developers.