![Alt](https://repobeats.axiom.co/api/embed/96ce2691516d9a6a2821e3b67c5280059efe89b2.svg "Repobeats analytics image")[![Discord](https://img.shields.io/badge/Discord-Join%20Server-7289DA?style=for-the-badge&logo=discord&logoColor=white)](https://discord.gg/fAbqNjwG)

[![Latest Stable Version](https://poser.pugx.org/batteryincluded/batteryincluded-php-sdk/v/stable)](https://packagist.org/packages/batteryincluded/batteryincluded-php-sdk) [![Packagist](https://img.shields.io/packagist/dt/batteryincluded/batteryincluded-php-sdk.svg)](https://packagist.org/packages/batteryincluded/batteryincluded-php-sdk)

# batteryincluded-php-sdk
The SDK is licensed under the [MIT License](LICENSE). Feel free to contribute!

## Using the SDK

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

## Community

Join our community on [Discord](https://discord.gg/fAbqNjwG) to ask questions, give feedback, or connect with other developers.