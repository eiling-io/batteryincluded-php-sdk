# AGENTS.md

Guidance for AI coding agents working **on this repository**. To integrate the SDK into a shop, read
[`docs/llms-full.txt`](docs/llms-full.txt) instead.

## Project

Official PHP SDK for [BatteryIncluded](https://batteryincluded.ai) (search, suggestions, recommendations).
Package: `batteryincluded/batteryincluded-php-sdk`. Docs: https://sdk.batteryincluded.eiling.io/

- PHP `^8.2`, extensions `curl` and `mbstring`, **no runtime dependencies**. Do not add any.
- Namespace `BatteryIncludedSdk\` → `src/`, tests `BatteryIncludedSdkTests\` → `tests/`.

## Layout

| Path | Content |
|---|---|
| `src/Client/` | `ApiClient` (base url, collection, api key, user agent), `CurlHttpClient` (timeouts), `HttpClientInterface` |
| `src/Exception/` | `BatteryIncludedException` (base), `NetworkException`, `InvalidDocumentException` |
| `src/Dto/` | `ProductBaseDto`, `BlogBaseDto`, `CategoryDto`, `ProductPropertyDto`, `AbstractTranslatableDto` |
| `src/Service/` | `SyncService` (import, full/batched import, partial update, delete), `Response` |
| `src/Shop/` | `BrowseService`, `BrowseSearchStruct`, `BrowseResponse`, facet DTOs, extensions |
| `src/Suggest/`, `src/SimilarSearch/`, `src/Highlights/` | Suggestions |
| `src/Recommendations/`, `src/CartRecommendations/`, `src/Presets/` | Recommendations, presets |
| `src/I18n/I18nResolver.php` | Resolves `_<TYPE>_i18n` nodes into `_<TYPE>` |
| `src/Availability/AvailabilityResolver.php`, `src/Dto/AbstractAvailabilityDto.php` | `_<TYPE>_availability` per country or sales channel |
| `src/Polyfill/mbstring.php` | `mb_trim()` for PHP < 8.4, autoloaded via `composer.json` `files` |
| `tests/` | Unit tests and live tests; `Helper.php` generates the test data, `DeviceImage.php` its SVG images |
| `examples/` | One script per service plus a demo shop (`index.php`, `detail.php`, `app/`, `assets/`) |
| `docs/` | GitHub Pages (Jekyll, just-the-docs, colour scheme `eiling`), English in `docs/`, German in `docs/de/` (jekyll-polyglot), built by `.github/workflows/pages.yml` |

## Commands

```bash
composer install
./vendor/bin/phpunit                                                   # all tests, includes live tests
./vendor/bin/phpunit --filter 'Dto|I18n|Polyfill|SearchStruct|BrowseResponse|SyncServiceRequest' # unit tests only
docker run --rm -v "$PWD":/app -w /app php:8.4-cli \
  php ./tools/php-cs-fixer fix --config=.php-cs-fixer.php              # code style, run on PHP 8.4
./tools/phpstan analyse --memory-limit=1G                              # static analysis, level 8 from phpstan.neon
make ddev-quality                                                      # all of the above in ddev
```

CI runs code style (dry run) and PHPStan (`phpstan.neon`, level 8, `src` and `tests`) on PHP 8.2, 8.3, 8.4 and 8.5. PHPUnit always talks to the live API, so it runs
in the job `tests (live api)` with the secrets of the GitHub environment `testing`, which a maintainer has to approve
(pushes to `main` use `testing-main`, limited to the branch `main`, without approval).
Do not move the secrets back to repository secrets.

## Rules

- Run the CS fixer on **PHP 8.4**. Its rule `mb_str_functions` turns `trim()` into `mb_trim()`; the polyfill keeps that
  working on 8.2/8.3. Never replace `mb_trim()` by `trim()`.
- `phpunit.xml` requires coverage metadata: every test class needs `#[CoversClass]`/`#[UsesClass]` (or
  `#[CoversNothing]`) for each `src/` class it touches.
- Keep `docs/` and `docs/llms-full.txt` in sync with public API changes.
- **Docs are always written in English and German.** Every page exists twice: `docs/<page>.md` (`lang: en`) and
  `docs/de/<page>.md` (`lang: de`, same `permalink` and `nav_order`). Never add, change or remove a page in one language
  only, update both in the same commit. Code blocks are identical, only the text is translated (German with "du").
  Headings keep the same order, the German ones carry the English anchor (`## Filter {#filters}`), callouts are
  `.hinweis`/`.wichtig`/`.achtung`. Run `python3 docs/_tools/check-translations.py` before committing, the pages
  workflow fails otherwise. `llms.txt` and `llms-full.txt` stay English only.
- Follow the existing style: short comments that explain *why*, `declare(strict_types=1)` in every file.
- Setters of DTOs and search structs return `static` (fluent). Record every user facing change in `CHANGELOG.md`,
  breaking changes need an upgrade note.

## Domain knowledge

- Documents are exported as `{"id": "PRODUCT-1", "type": "PRODUCT", "_PRODUCT": {...}}`. The id is prefixed with the
  type; fields live in the type node. `null` values are skipped unless `exportNullValues()` is called.
- Translations: `_PRODUCT_i18n.<locale>.<field>` next to `_PRODUCT`. Translated fields **stay** in `_PRODUCT` as
  default values (deliberate decision, do not remove them). Locales are normalized to lower case (`de_CH` → `de-ch`).
- The gateway resolves `_PRODUCT_i18n` for `v[locale]` (no fallback, missing locale removes the node).
  `BrowseResponse::getHits()` merges it into `_PRODUCT`, `getRawHits()` returns it untouched.
- Always send `v[locale]` in lower case. `BrowseService`, `SuggestService` and `SimilarSearchService` normalize it.
  The suggest endpoint returns HTTP 500 without locale once translated facets are configured.
- Availability: `_PRODUCT_availability.<scope>.{active,price,instock}` overwrites `_PRODUCT` for `v[availability]`
  (`BrowseSearchStruct::setAvailability()`, sent only when set). It is a separate module next to i18n, do not merge
  the two. `getHits()` resolves i18n first, then availability. No fallback between scopes, `false`/`0` overwrite.
- Api errors (4xx/5xx) never throw: `Response::getBody()` is `[]`, so every typed response renders empty; callers check
  `isSuccessful()`. Response classes must tolerate any body (missing keys, scalars). Only unreachable apis
  (`NetworkException`) and unencodable documents (`InvalidDocumentException`) throw.
- `BrowseResponse::getFacets()` skips facets of other locales (`_PRODUCT_i18n.<locale>.*`) and detects the category
  tree (`_<TYPE>_i18n.categories` before `_<TYPE>.categories`) unless a field is passed.
- Filters, suggest fields and relevance are configured in the BatteryIncluded portal, not in the SDK
  (see `docs/backend-configuration.md`).

## Live tests

- The live tests sync `Helper::generateProducts(20)` (720 products) into the collection from the `COLLECTION` and
  `APIKEY` environment variables and **overwrite its documents**. Use a dedicated test collection.
- `tests/sync-demo-data.php` (run as last step of the CI job `tests (live api)`) and
  `examples/sync/sync_full_product_and_blogs.php` run a full import: all other documents of the collection are deleted.
- Never commit credentials: `examples/credentials.php` and `phpunit.xml` stay without real keys.
