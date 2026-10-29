# Changelog

All notable changes of the SDK. The project follows [semantic versioning](https://semver.org) from 1.0.0 on.

## 1.0.0

### Upgrade from 0.7.x

1. **Reimport your collection once.** Documents are identified only by the root id `PRODUCT-1002` (type prefix plus
   the identifier of the constructor), `_PRODUCT.id` and `_BLOG.id` are no longer exported. The import merges
   documents, so an old `_PRODUCT.id` stays in the index until the documents are deleted and imported again.
2. The root `type` is exported in upper case like the id prefix and the type node: `new BlogBaseDto('1', 'faq')`
   becomes `{"id": "FAQ-1", "type": "FAQ", "_FAQ": {...}}`. Filters on a lower case custom type have to be adjusted.
3. Replace removed methods:

   | Removed | Replacement |
   |---|---|
   | `ProductBaseDto::setId()/getId()`, `BlogBaseDto::setId()/getId()` | The identifier of the constructor |
   | `SuggestService::suggest($query, $locale)` | `suggestWithFilter()` with a `SuggestSearchStruct` |
   | Second argument of `ApiClient::getJson()` | Nothing, it was never used |

4. Setters of the DTOs and search structs return `static` instead of `void`. Subclasses that override a setter must
   change its return type to `static`.
5. `Response::getBody()` returns `[]` for every error response (4xx/5xx), not only for 404. Read the error with
   `isSuccessful()` and `getErrorMessage()`.
6. `CurlHttpClient` throws `NetworkException` instead of `\Exception` (still a subclass of `\Exception`). Reads abort
   after 10 seconds and connections after 5 seconds, imports, updates and deletes have no limit. Configure them with
   `new CurlHttpClient(readTimeout: 2.0, connectTimeout: 1.0, writeTimeout: 600.0)`.
7. `BrowseResponse::getFacets()` detects the category tree and skips the facets of other locales. Remove your own
   filters for `_PRODUCT_i18n.<locale>.*` facets.

### Added

- Translations (`_<TYPE>_i18n`): `addTranslation()`, `addCategoryTranslation()`, `addPropertyTranslation()`,
  `setPropertiesTranslation()`, locale on every search, suggest and recommendation request, `I18nResolver`.
- Availability per country or sales channel (`_<TYPE>_availability`): `setAvailability()`, `addAvailability()`,
  `v[availability]` for browse, suggest and recommendations, `AvailabilityResolver`.
- `SuggestResponse::getResolvedDocuments()` merges translations and availability like `BrowseResponse::getHits()`.
- Availability scope as last argument of `recommendByIdentifier()` and `recommendByIdentifiers()`.
- `Response::isSuccessful()` and `Response::getErrorMessage()`.
- Exceptions `BatteryIncludedException`, `NetworkException` and `InvalidDocumentException`.
- `CurlHttpClient` timeouts and compressed responses, a `User-Agent` with the SDK and PHP version on every request.
- Documentation at https://sdk.batteryincluded.eiling.io/ in English and German, `docs/llms-full.txt` for coding agents.

### Fixed

- Error responses of the api no longer cause a `TypeError` in the suggest, recommendation and preset responses or a
  warning in `BrowseResponse::getFound()`.
- `BrowseResponse` keeps the status code of the api.
- `BrowseResponse::getFacets()` no longer fails for facets without hits.
- Invalid UTF-8 in documents is replaced instead of sending an empty line, values json cannot represent throw an
  `InvalidDocumentException` with the document id.
- The transaction id of a batched full import is url encoded.
- Recommendations accept the type in lower case (`blog` becomes `BLOG-1`).
