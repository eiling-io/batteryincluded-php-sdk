<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\I18n;

/**
 * Merges the translation nodes of a document (`_PRODUCT_i18n`, `_BLOG_i18n`, ...) into their payloads (`_PRODUCT`, `_BLOG`, ...).
 *
 * Exported document, the language dependent fields are scoped by locale:
 *   _PRODUCT: { price: 699 }, _PRODUCT_i18n: { de: { name: 'Fernseher' }, en: { name: 'TV' } }
 *
 * The gateway resolves the node for the requested locale (`v[locale]`, or the default of the collection variable):
 *   _PRODUCT: { price: 699 }, _PRODUCT_i18n: { name: 'TV' }
 *
 * Resolved document for locale `en`:
 *   _PRODUCT: { price: 699, name: 'TV' }
 *
 * The gateway removes the node if the requested locale is missing, the values of the payload remain as fallback.
 */
final class I18nResolver
{
    public const SUFFIX = '_i18n';

    public static function normalizeLocale(string $locale): string
    {
        return mb_strtolower(str_replace('_', '-', trim($locale)));
    }

    /**
     * Translation node of a type, e.g. `PRODUCT` => `_PRODUCT_i18n`.
     */
    public static function getKey(string $type): string
    {
        return '_' . mb_strtoupper($type) . self::SUFFIX;
    }

    public static function resolveHits(array $hits, string $locale): array
    {
        return array_map(static function ($hit) use ($locale) {
            if (!is_array($hit)) {
                return $hit;
            }

            if (isset($hit['document']) && is_array($hit['document'])) {
                $hit['document'] = self::resolveDocument($hit['document'], $locale);

                return $hit;
            }

            return self::resolveDocument($hit, $locale);
        }, $hits);
    }

    public static function resolveDocument(array $document, string $locale): array
    {
        foreach ($document as $key => $translations) {
            if (preg_match('/^(_.+)' . self::SUFFIX . '$/', (string) $key, $matches) !== 1) {
                continue;
            }

            unset($document[$key]);

            $payloadKey = $matches[1];
            if (!is_array($translations) || !isset($document[$payloadKey]) || !is_array($document[$payloadKey])) {
                continue;
            }

            $document[$payloadKey] = self::mergeTranslations($document[$payloadKey], self::findTranslations($translations, $locale));
        }

        return $document;
    }

    public static function mergeTranslations(array $payload, array $translations): array
    {
        foreach ($translations as $field => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $payload[$field] = $value;
        }

        return $payload;
    }

    /**
     * Translations of the locale, falling back from `de-ch` to `de`.
     * A node which is not scoped by locales has already been resolved by the gateway.
     */
    private static function findTranslations(array $translations, string $locale): array
    {
        $translationsByLocale = [];
        foreach ($translations as $key => $fields) {
            if (!is_array($fields) || array_is_list($fields) || preg_match('/^[a-z]{2}([-_][a-z0-9]{2,8})*$/i', (string) $key) !== 1) {
                return $translations;
            }

            $translationsByLocale[self::normalizeLocale((string) $key)] = $fields;
        }

        $locale = self::normalizeLocale($locale);
        if ($locale === '') {
            return [];
        }

        return $translationsByLocale[$locale] ?? $translationsByLocale[explode('-', $locale)[0]] ?? [];
    }
}
