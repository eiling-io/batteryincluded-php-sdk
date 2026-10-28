---
lang: de
permalink: /development.html
title: Entwicklung
nav_order: 10
---

# Entwicklung
{: .no_toc }

1. TOC
{:toc}

Beiträge sind willkommen. Diese Seite beschreibt, wie du die Qualitätswerkzeuge ausführst, bevor du einen Pull Request
öffnest.

## Einrichtung {#setup}

```bash
ddev start
ddev composer install
```

Alle folgenden Befehle funktionieren auch ohne DDEV, wenn PHP und Composer lokal installiert sind.

## Tests {#tests}

```bash
make ddev-unit
# or
./vendor/bin/phpunit
```

Die Suite enthält Unit-Tests und Live-Tests gegen die BatteryIncluded API. Die Live-Tests synchronisieren die Testdaten
aus `tests/Helper.php` in die Collection aus den Umgebungsvariablen `COLLECTION` und `APIKEY`. Setze sie in einer
lokalen `phpunit.xml` (Kopie von `phpunit.xml.dist`, von Git ignoriert) oder als Secrets in der CI.

{: .achtung }
Verwende eine eigene Test-Collection, die Live-Tests überschreiben ihre Dokumente.

Die Live-Tests synchronisieren nur Produkte. Um die kompletten Demodaten (720 Produkte und 10 Blogbeiträge)
wiederherzustellen, führe einen Vollimport aus. Die CI macht das am Ende jedes Laufs:

```bash
COLLECTION=customer.my-test-collection APIKEY=... php tests/sync-demo-data.php
```

## Code Style {#code-style}

```bash
make ddev-cs
# or
./tools/php-cs-fixer fix --config=.php-cs-fixer.php
```

Das Regelwerk aktiviert `mb_str_functions`. Unter PHP ≥ 8.4 ersetzt der Fixer daher `trim()` durch `mb_trim()`; das SDK
bringt für ältere Versionen ein Polyfill mit, das Ergebnis ist also auf allen unterstützten PHP-Versionen gleich.

## Statische Analyse {#static-analysis}

```bash
make ddev-phpstan
# or
./tools/phpstan analyse -l 5 ./src/ ./tests/
```

## Alle Prüfungen {#all-checks}

```bash
make ddev-quality
```

Die CI führt Code Style und PHPStan auf PHP 8.2, 8.3, 8.4 und 8.5 ohne Secrets aus. Die Tests sprechen mit der
Live-API und laufen im Job `tests (live api)` mit den Secrets der GitHub-Umgebung `testing`: Ein Maintainer muss den
Lauf freigeben (Required Reviewers der Umgebung), nachdem er den Code geprüft hat. Pushes auf `main` verwenden
stattdessen die Umgebung `testing-main`, die dieselben Secrets hat, keine Reviewer und auf den Branch `main` beschränkt
ist. Der Job führt die Tests nacheinander auf allen PHP-Versionen aus und stellt zum Schluss mit
`tests/sync-demo-data.php` die Demodaten wieder her, auch wenn die Tests fehlgeschlagen sind. Pull Requests aus Forks
bekommen keine Secrets.

## Dokumentation {#documentation}

Diese Dokumentation liegt in `docs/` und wird vom Workflow `.github/workflows/pages.yml` gebaut und auf GitHub Pages
veröffentlicht (Jekyll mit just-the-docs und jekyll-polyglot). Die englischen Seiten liegen in `docs/`, ihre deutschen
Gegenstücke in `docs/de/` mit demselben `permalink` und `lang: de`. Ändere beide Sprachen im selben Pull Request.
Lokale Vorschau:

```bash
cd docs
bundle install
bundle exec jekyll serve
```

Die englische Fassung liegt dann unter `http://127.0.0.1:4000/`, die deutsche unter `http://127.0.0.1:4000/de/`.
