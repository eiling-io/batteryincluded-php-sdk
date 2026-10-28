---
lang: de
permalink: /demo-shop.html
title: Demo-Shop
nav_order: 9
---

# Demo-Shop
{: .no_toc }

1. TOC
{:toc}

Das Verzeichnis `examples` enthält ein komplettes Shop-Frontend, gebaut mit dem SDK. Nutze es als
Referenzimplementierung oder um Änderungen gegen deine eigene Collection auszuprobieren.

## Funktionen {#features}

- Suche mit Vorschlägen während der Eingabe (`examples/api/suggest.php`)
- Facetten in der Seitenleiste: Kategoriebaum, Auswahl-, Bereichs- und Bewertungsfilter sowie ein Typfilter für
  Produkte und Blogbeiträge
- Sortierung, Blättern, Teaser, Promotions, Weiterleitungen und Aktionscodes
- Sprachwechsel (Deutsch / Englisch) mit [übersetzten Dokumenten](translations)
- Länderwechsel (Deutschland / Österreich / Schweiz) mit [Preisen und Status pro Land](availability), Produkte, die in
  einem Land nicht verkauft werden, sind ausgegraut
- Detailseite mit allen Feldern eines Dokuments, ihrer Herkunft (Übersetzung oder Standardwert) und der rohen
  API-Antwort
- Heller und dunkler Modus

## Starten {#running-it}

Das Repository bringt eine [DDEV](https://ddev.com)-Konfiguration mit `examples/` als Document Root mit:

```bash
ddev start
ddev composer install
```

Lege `examples/credentials.php` an (Git ignoriert die Datei) und trage deine Collection und deinen API-Key ein:

```php
<?php

$collection = 'customer.my-shop';   // keep the prefix "customer."
$apiKey = 'your-api-key';
```

Öffne dann `https://batteryincluded-php-sdk.ddev.site`.

Ohne DDEV funktioniert auch der eingebaute PHP-Server:

```bash
composer install
php -S 127.0.0.1:8000 -t examples
```

## Testdaten {#test-data}

`examples/sync/sync_full_product_and_blogs.php` füllt die Collection mit den Testdaten der Testsuite: 720 Produkte
(Smartphones, Tablets und Laptops in vier Farben und drei Speichergrößen, mit deutschen und englischen Übersetzungen und
generierten SVG-Bildern) und 10 Blogbeiträge.

{: .achtung }
Das Skript führt einen Vollimport aus, alle anderen Dokumente der Collection werden gelöscht.

## Aufbau {#structure}

| Datei | Inhalt |
|---|---|
| `examples/index.php` | Suchergebnisseite |
| `examples/detail.php` | Detailseite eines Produkts oder Blogbeitrags |
| `examples/api/suggest.php` | JSON-Endpunkt für die Vorschläge |
| `examples/app/bootstrap.php` | Konfiguration und Hilfsfunktionen |
| `examples/app/translations.php` | Oberflächentexte pro Sprache |
| `examples/app/partials/` | Gemeinsamer Header und Footer |
| `examples/assets/` | Styles und Skripte |

Daneben liegen Einzelbeispiele für jeden Service, zum Beispiel `examples/sync/`, `examples/shop/` und
`examples/recommendations/`.
