<?php

declare(strict_types=1);

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Shop\FacetDto;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../credentials.php';

/**
 * Languages of the demo shop.
 *
 * - locale: sent to the api as `v[locale]`, the gateway resolves `_PRODUCT_i18n.<locale>` and the sdk merges it into the hits
 */
const DEMO_LANGUAGES = [
    'de' => ['label' => 'Deutsch', 'flag' => '🇩🇪', 'locale' => 'de', 'intl' => 'de_DE'],
    'en' => ['label' => 'English', 'flag' => '🇬🇧', 'locale' => 'en', 'intl' => 'en_GB'],
];
const DEMO_DEFAULT_LANGUAGE = 'de';
const DEMO_LANGUAGE_COOKIE = 'bi_demo_lang';
const DEMO_SORTINGS = [
    '' => 'sort_relevance',
    '_PRODUCT.price:asc' => 'sort_price_asc',
    '_PRODUCT.price:desc' => 'sort_price_desc',
    '_PRODUCT.rating:desc' => 'sort_rating_desc',
];
const DEMO_PER_PAGE = [12, 24, 48];

function demoApiClient(): ApiClient
{
    global $collection, $apiKey;

    return new ApiClient(
        new CurlHttpClient(),
        'https://api.batteryincluded.io/api/v1/collections/',
        $collection,
        $apiKey
    );
}

/**
 * Language from the query, the cookie or the browser, in this order.
 */
function demoResolveLanguage(): string
{
    $requested = mb_strtolower((string) ($_GET['lang'] ?? ''));
    if (isset(DEMO_LANGUAGES[$requested])) {
        if (($_COOKIE[DEMO_LANGUAGE_COOKIE] ?? '') !== $requested && !headers_sent()) {
            setcookie(DEMO_LANGUAGE_COOKIE, $requested, ['expires' => time() + 31536000, 'path' => '/', 'samesite' => 'Lax']);
        }

        return $requested;
    }

    $cookie = (string) ($_COOKIE[DEMO_LANGUAGE_COOKIE] ?? '');
    if (isset(DEMO_LANGUAGES[$cookie])) {
        return $cookie;
    }

    foreach (explode(',', (string) ($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '')) as $accepted) {
        $language = mb_strtolower(mb_substr(mb_trim($accepted), 0, 2));
        if (isset(DEMO_LANGUAGES[$language])) {
            return $language;
        }
    }

    return DEMO_DEFAULT_LANGUAGE;
}

function translation(string $key): mixed
{
    static $translations = null;
    $translations ??= require __DIR__ . '/translations.php';

    return $translations[$GLOBALS['lang']][$key] ?? $translations[DEMO_DEFAULT_LANGUAGE][$key] ?? $key;
}

function t(string $key, mixed ...$args): string
{
    $text = (string) translation($key);

    return $args === [] ? $text : vsprintf($text, $args);
}

/**
 * Url of an asset with its modification time, so browsers load changes immediately instead of a cached version.
 */
function asset(string $path): string
{
    $file = __DIR__ . '/../' . $path;

    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Escapes an api highlight but keeps the <mark> tags.
 */
function highlight(string $value): string
{
    return str_replace(['&lt;mark&gt;', '&lt;/mark&gt;'], ['<mark>', '</mark>'], e($value));
}

/**
 * Filters of the request, empty values and incomplete ranges are removed.
 */
function demoRequestFilters(): array
{
    $filters = [];
    foreach ((array) ($_GET['f'] ?? []) as $field => $values) {
        if (!is_array($values)) {
            continue;
        }

        if (array_key_exists('from', $values) || array_key_exists('till', $values)) {
            $from = mb_trim((string) ($values['from'] ?? ''));
            $till = mb_trim((string) ($values['till'] ?? ''));
            if (is_numeric($from) || is_numeric($till)) {
                $filters[$field] = [
                    'from' => is_numeric($from) ? $from : '0',
                    'till' => is_numeric($till) ? $till : (string) PHP_INT_MAX,
                ];
            }
            continue;
        }

        $values = array_values(array_unique(array_filter(array_map('strval', $values), static fn ($value) => $value !== '')));
        if ($values !== []) {
            $filters[$field] = $values;
        }
    }

    return $filters;
}

/**
 * Url of the current page with the given parameters replaced, null removes a parameter.
 */
function url(array $overrides = []): string
{
    $params = array_merge($GLOBALS['state'], $overrides);
    $params = array_filter($params, static fn ($value) => $value !== null && $value !== '' && $value !== []);
    if (($params['page'] ?? 1) === 1) {
        unset($params['page']);
    }

    return 'index.php' . ($params === [] ? '' : '?' . http_build_query($params));
}

/**
 * Url of the detail page of a document, e.g. `PRODUCT-5`.
 */
function detailUrl(string $id, ?string $language = null): string
{
    return 'detail.php?' . http_build_query(['id' => $id, 'lang' => $language ?? $GLOBALS['lang']]);
}

/**
 * Nested values as flat list with dotted keys, e.g. `properties.0.Color` => `Blue`.
 *
 * @return array<string, mixed>
 */
function flattenData(array $data, string $prefix = ''): array
{
    $flat = [];
    foreach ($data as $key => $value) {
        $path = $prefix === '' ? (string) $key : $prefix . '.' . $key;
        // lists of scalars (e.g. categories) are kept as one value
        $isNested = is_array($value) && $value !== [] && (!array_is_list($value) || array_filter($value, 'is_array') !== []);
        if ($isNested) {
            $flat += flattenData($value, $path);
        } else {
            $flat[$path] = $value;
        }
    }

    return $flat;
}

function urlWithoutFilter(string $field, ?string $value = null): string
{
    $filters = $GLOBALS['state']['f'];
    if ($value === null) {
        unset($filters[$field]);
    } else {
        $filters[$field] = array_values(array_diff($filters[$field] ?? [], [$value]));
    }

    return url(['f' => $filters, 'page' => null]);
}

function formatPrice(float $price): string
{
    if (class_exists(NumberFormatter::class)) {
        return (string) (new NumberFormatter(DEMO_LANGUAGES[$GLOBALS['lang']]['intl'], NumberFormatter::CURRENCY))->formatCurrency($price, 'EUR');
    }

    return number_format($price, 2, ',', '.') . ' €';
}

function formatNumber(float $number): string
{
    if (class_exists(NumberFormatter::class)) {
        return (string) (new NumberFormatter(DEMO_LANGUAGES[$GLOBALS['lang']]['intl'], NumberFormatter::DECIMAL))->format($number);
    }

    return (string) $number;
}

function formatDate(string $date): string
{
    $timestamp = strtotime($date);
    if ($timestamp === false) {
        return $date;
    }

    if (class_exists(IntlDateFormatter::class)) {
        return (string) (new IntlDateFormatter(DEMO_LANGUAGES[$GLOBALS['lang']]['intl'], IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE))->format($timestamp);
    }

    return date('d.m.Y', $timestamp);
}

function stars(float $rating): string
{
    $html = '<span class="stars" role="img" aria-label="' . e(t('stars', formatNumber($rating))) . '">';
    for ($i = 1; $i <= 5; ++$i) {
        $icon = $rating >= $i ? 'bi-star-fill' : ($rating >= $i - 0.5 ? 'bi-star-half' : 'bi-star');
        $html .= '<i class="bi ' . $icon . '" aria-hidden="true"></i>';
    }

    return $html . '</span>';
}

/**
 * Label of a facet value, e.g. `BLOG` => `Blogbeiträge` for the type facet.
 */
function facetValueLabel(string $field, string $value): string
{
    return translation('facet_values')[$field][mb_strtoupper($value)] ?? $value;
}

/**
 * Translated categories of the language, the untranslated categories as long as the api delivers none.
 */
function demoCategoryField(array $facetCounts): string
{
    $translatedField = I18nResolver::getKey('PRODUCT') . '.categories';

    return in_array($translatedField, array_column($facetCounts, 'field_name'), true) ? $translatedField : '_PRODUCT.categories';
}

/**
 * Facets which are shown in the sidebar.
 *
 * The gateway resolves the translated facets for the requested locale (`_PRODUCT_i18n.*`),
 * facets which are still scoped by another locale (`_PRODUCT_i18n.en.*`) are hidden.
 *
 * @param FacetDto[] $facets
 *
 * @return array<string, array{facet: FacetDto, label: string}>
 */
function demoVisibleFacets(array $facets, string $language, string $categoryField): array
{
    $visible = [];
    foreach ($facets as $facet) {
        $field = $facet->getFieldName();
        // the type facet stays visible, e.g. to hide blog posts
        $isTechnicalFacet = str_starts_with($field, '_availability.');
        $isOtherCategoryFacet = str_ends_with($field, 'categories') && $field !== $categoryField;
        $isFacetOfOtherLanguage = preg_match('/^_[A-Z]+' . I18nResolver::SUFFIX . '\.([a-z]{2}(-[a-z0-9]+)*)\./', $field, $matches) === 1
            && $matches[1] !== DEMO_LANGUAGES[$language]['locale'];
        if ($isTechnicalFacet || $isOtherCategoryFacet || $isFacetOfOtherLanguage) {
            continue;
        }

        // labels are translated in the backend, the field name is used as long as a label is missing
        $label = translation('facet_labels')[$field] ?? ($facet->getFieldLabel() !== $field ? $facet->getFieldLabel() : null);
        $label ??= $field === $categoryField ? t('categories') : mb_substr($field, mb_strrpos($field, '.') + 1);

        $visible[$field] = ['facet' => $facet, 'label' => $label];
    }

    // categories first, everything else in api order
    uksort($visible, static fn ($a, $b) => ($b === $categoryField) <=> ($a === $categoryField));

    return $visible;
}

/**
 * Category tree as navigation, every node with subcategories can be expanded, the selected path is expanded initially.
 */
function categoryTree(array $nodes, string $field, string $parentPath = '', bool $expanded = true): string
{
    // natural order, the api delivers e.g. "Laptop 9 Pro" before "Laptop 10 Pro" only by chance
    uksort($nodes, static fn ($a, $b) => strnatcasecmp((string) $a, (string) $b));

    $id = 'category-' . md5($field . "\0" . $parentPath);
    $html = '<ul class="category-tree list-unstyled mb-0' . ($parentPath === '' ? '' : ' collapse' . ($expanded ? ' show' : '')) . '" id="' . e($id) . '">';
    foreach ($nodes as $name => $node) {
        $path = $parentPath === '' ? (string) $name : $parentPath . ' > ' . $name;
        $checked = $node['checked'] ?? false;
        $hasChildren = !empty($node['childs']);
        // a single root (e.g. the shop name) is always expanded, otherwise the tree would show only one entry
        $isExpanded = $checked || ($parentPath === '' && count($nodes) === 1);
        $childId = 'category-' . md5($field . "\0" . $path);
        $href = url(['f' => array_merge($GLOBALS['state']['f'], [$field => [$path]]), 'page' => null]);

        $html .= '<li><div class="category-row">';
        if ($hasChildren) {
            $html .= '<button class="category-toggle' . ($isExpanded ? '' : ' collapsed') . '" type="button" data-bs-toggle="collapse" data-bs-target="#' . e($childId) . '"'
                . ' aria-expanded="' . ($isExpanded ? 'true' : 'false') . '" aria-controls="' . e($childId) . '" aria-label="' . e(t('toggle_category', (string) $name)) . '">'
                . '<i class="bi bi-chevron-right" aria-hidden="true"></i></button>';
        } else {
            $html .= '<span class="category-toggle-spacer" aria-hidden="true"></span>';
        }
        $html .= '<a class="category-link' . ($checked ? ' active' : '') . '" href="' . e($href) . '"' . ($checked ? ' aria-current="true"' : '') . '>';
        $html .= '<span>' . e($name) . '</span>';
        if (isset($node['count'])) {
            $html .= '<span class="facet-count">' . e($node['count']) . '</span>';
        }
        $html .= '</a></div>';
        if ($hasChildren) {
            $html .= categoryTree($node['childs'], $field, $path, $isExpanded);
        }
        $html .= '</li>';
    }

    return $html . '</ul>';
}

/**
 * @return array{pages: list<int|null>, current: int, last: int}
 */
function pagination(int $current, int $last, int $window = 1): array
{
    $pages = [];
    for ($page = 1; $page <= $last; ++$page) {
        if ($page === 1 || $page === $last || abs($page - $current) <= $window) {
            $pages[] = $page;
        } elseif (end($pages) !== null) {
            $pages[] = null;
        }
    }

    return ['pages' => $pages, 'current' => $current, 'last' => $last];
}
