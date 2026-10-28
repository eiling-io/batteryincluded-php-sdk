---
lang: de
permalink: /syncing.html
title: Dokumente synchronisieren
nav_order: 3
---

# Dokumente synchronisieren
{: .no_toc }

1. TOC
{:toc}

Eine Collection enthält Dokumente eines oder mehrerer Typen, zum Beispiel Produkte und Blogbeiträge. Das SDK beschreibt
jedes Dokument als DTO und sendet sie als NDJSON an die Import-Endpunkte.

## Produkte {#products}

```php
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;

$product = new ProductBaseDto('1002');          // identifier of the product in your shop, exported as id PRODUCT-1002
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

So wird das Dokument exportiert:

```json
{
  "id": "PRODUCT-1002",
  "type": "PRODUCT",
  "_PRODUCT": {
    "name": "Ultra HD LED TV 65\"",
    "price": 699,
    "categories": ["Electronics", "Electronics > TVs"],
    "properties": [{"Color": ["Black"], "Display size": ["65"]}]
  }
}
```

- Die Dokument-ID ist der Identifier aus dem Konstruktor mit dem Typ als Präfix (`PRODUCT-1002`) und steht neben
  `type` im Root. Produkte und Blogbeiträge mit derselben Auto-Increment-ID kollidieren so nie, und Partial-Updates und
  Löschungen sprechen Dokumente über diese ID an. In `_PRODUCT` gibt es kein Feld `id`; `setId()` ist deprecated und
  wird nicht mehr exportiert. Ein `_PRODUCT.id` aus einem früheren Export bleibt im Index, bis das Dokument gelöscht
  und neu importiert wird.
- Alle Felder liegen in einem Knoten pro Typ (`_PRODUCT`). Filtere und sortiere mit diesem Präfix, zum Beispiel
  `_PRODUCT.price`.
- Ein Kategoriepfad wird mit allen übergeordneten Pfaden exportiert (`Electronics`, `Electronics > TVs`).
- Felder mit dem Wert `null` werden nicht exportiert (siehe [Felder leeren](#clearing-fields)).

{: .hinweis }
Halte Eigenschaftsnamen stabil und sprachunabhängig (zum Beispiel immer `Color`). Die Bezeichnungen werden im
BatteryIncluded Backend übersetzt, die Werte über [Übersetzungen](translations).

## Blogbeiträge {#blog-posts}

```php
use BatteryIncludedSdk\Dto\BlogBaseDto;

$blog = new BlogBaseDto('1');
$blog->setTitle('Top 5 smartphones');
$blog->setAuthor('Jane Doe');
$blog->setPublishedAt('2026-06-01');
$blog->setActive(true);
$blog->setShortDescription('Our favourites of the year.');
$blog->setDescription('…');
$blog->setPreviewImage('https://shop.example/blog/top-5.png');
$blog->setBlogUrl('https://shop.example/blog/top-5');
```

Blogbeiträge werden als `BLOG-1` mit ihren Feldern in `_BLOG` gespeichert. Produkte und Blogbeiträge lassen sich in
einer Anfrage synchronisieren und gemeinsam durchsuchen, siehe [Nach Typ filtern](search#filtering-by-type).

## Sync-Methoden {#sync-methods}

```php
use BatteryIncludedSdk\Service\SyncService;

$syncService = new SyncService($apiClient);
```

| Methode | Endpunkt | Wofür |
|---|---|---|
| `syncOneOrManyElements(...$dtos)` | `POST /documents/import` | Die übergebenen Dokumente anlegen oder ersetzen |
| `syncFullElements(...$dtos)` | `POST /documents/import?full=1` | Vollimport, **nicht enthaltene Dokumente werden gelöscht** |
| `syncFullBatchElements($transactionId, $finished, ...$dtos)` | `POST /documents/import?transactionId=…` | Vollimport, aufgeteilt auf mehrere Anfragen |
| `partialUpdateOneOrManyElements(...$dtos)` | `PATCH /documents/update` | Nur die übergebenen Felder aktualisieren |
| `deleteElementsByIds(...$ids)` | `DELETE /documents/delete` | Dokumente entfernen |

Jede Methode gibt eine `Response` mit einem Ergebnis pro Dokument zurück:

```php
$result = $syncService->syncOneOrManyElements($product, $blog);
// [['id' => 'PRODUCT-1002', 'success' => true], ['id' => 'BLOG-1', 'success' => true]]
```

### Vollimport in Batches {#full-import-in-batches}

Große Kataloge passen nicht in eine einzelne Anfrage. Sende sie in Teilen mit derselben Transaktions-ID und markiere die
letzte Anfrage als abgeschlossen. Erst dann entfernt BatteryIncluded alle Dokumente, die nicht Teil der Transaktion
waren.

```php
$transactionId = 'full-import-' . date('YmdHis');
$chunks = array_chunk($products, 500);
$last = array_key_last($chunks);

foreach ($chunks as $index => $chunk) {
    $syncService->syncFullBatchElements($transactionId, $index === $last, ...$chunk);
}
```

### Partial-Updates {#partial-updates}

Sende nur die geänderten Felder, zum Beispiel Preis und Bestand:

```php
$product = new ProductBaseDto('1002');
$product->setPrice(649.0);
$product->setInstock(9);

$syncService->partialUpdateOneOrManyElements($product);
```

Der Index wird asynchron aktualisiert: Die Antwort meldet sofort `success`, in der Suche ist die Änderung erst nach
wenigen Sekunden sichtbar.

### Felder leeren {#clearing-fields}

Felder mit dem Wert `null` werden beim Export übersprungen, damit ein Partial-Update nie versehentlich ein Feld leert.
Um `null` ausdrücklich zu senden, aktiviere `exportNullValues()`:

```php
$product = new ProductBaseDto('1002');
$product->setRating(null);
$product->exportNullValues();

$syncService->partialUpdateOneOrManyElements($product);
```

### Dokumente löschen {#deleting-documents}

```php
$syncService->deleteElementsByIds('PRODUCT-240', 'BLOG-2'); // root ids, prefixed with the type
```

## Eigene Felder {#custom-fields}

`ProductBaseDto` deckt die Standardfelder ab. Für shopspezifische Felder erweiterst du die Klasse und fügst die Felder
dem Knoten `_PRODUCT` hinzu:

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

`filterJsonValues()` entfernt `null`-Werte und berücksichtigt `exportNullValues()`. Ein vollständiges Beispiel ist
[`examples/extension/product.php`](https://github.com/eiling-io/batteryincluded-php-sdk/blob/main/examples/extension/product.php).

## Eigene Dokumenttypen {#custom-document-types}

Der Typ ist das zweite Argument des Konstruktors. Damit speicherst du weitere Inhalte, zum Beispiel Filialen oder
FAQ-Einträge:

```php
$faq = new BlogBaseDto('42', 'faq'); // stored as id FAQ-42, type FAQ, with the fields in _FAQ (always upper case)
```
