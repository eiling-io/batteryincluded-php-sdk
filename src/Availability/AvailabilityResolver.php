<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Availability;

/**
 * Merges the availability nodes of a document (`_PRODUCT_availability`) into their payloads (`_PRODUCT`).
 * Works like the I18nResolver, but with its own gateway variable and without fallback between scopes.
 *
 * Exported document, status, price and stock are scoped by e.g. country or sales channel:
 *   _PRODUCT: { active: true, price: 999, instock: 5 }, _PRODUCT_availability: { de: { price: 1337 }, ch: { active: false } }
 *
 * The gateway resolves the node for the requested scope (`v[availability]`, or the default of the collection variable):
 *   _PRODUCT: { active: true, price: 999, instock: 5 }, _PRODUCT_availability: { active: false }
 *
 * Resolved document for scope `ch`:
 *   _PRODUCT: { active: false, price: 999, instock: 5 }
 *
 * The gateway removes the node if the requested scope is missing, the values of the payload remain as fallback.
 */
final class AvailabilityResolver
{
    public const SUFFIX = '_availability';

    public const VARIABLE = 'availability';

    public static function normalizeScope(string $scope): string
    {
        return mb_strtolower(str_replace('_', '-', mb_trim($scope)));
    }

    /**
     * Availability node of a type, e.g. `PRODUCT` => `_PRODUCT_availability`.
     */
    public static function getKey(string $type): string
    {
        return '_' . mb_strtoupper($type) . self::SUFFIX;
    }

    public static function resolveHits(array $hits, string $scope): array
    {
        return array_map(static function ($hit) use ($scope) {
            if (!is_array($hit)) {
                return $hit;
            }

            if (isset($hit['document']) && is_array($hit['document'])) {
                $hit['document'] = self::resolveDocument($hit['document'], $scope);

                return $hit;
            }

            return self::resolveDocument($hit, $scope);
        }, $hits);
    }

    public static function resolveDocument(array $document, string $scope): array
    {
        foreach ($document as $key => $availabilities) {
            if (preg_match('/^(_.+)' . self::SUFFIX . '$/', (string) $key, $matches) !== 1) {
                continue;
            }

            unset($document[$key]);

            $payloadKey = $matches[1];
            if (!is_array($availabilities) || !isset($document[$payloadKey]) || !is_array($document[$payloadKey])) {
                continue;
            }

            $document[$payloadKey] = self::mergeAvailability($document[$payloadKey], self::findAvailability($availabilities, $scope));
        }

        return $document;
    }

    /**
     * Empty values keep the value of the payload, `0` and `false` overwrite it (inactive, out of stock).
     */
    public static function mergeAvailability(array $payload, array $availability): array
    {
        foreach ($availability as $field => $value) {
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $payload[$field] = $value;
        }

        return $payload;
    }

    /**
     * Availability of the scope. A node which is not scoped has already been resolved by the gateway.
     */
    private static function findAvailability(array $availabilities, string $scope): array
    {
        $availabilityByScope = [];
        foreach ($availabilities as $key => $fields) {
            if (!is_array($fields) || array_is_list($fields) || preg_match('/^[a-z0-9]+([-_][a-z0-9]+)*$/i', (string) $key) !== 1) {
                return $availabilities;
            }

            $availabilityByScope[self::normalizeScope((string) $key)] = $fields;
        }

        $scope = self::normalizeScope($scope);
        if ($scope === '') {
            return [];
        }

        // no fallback from `de-b2b` to `de`, a price must never be taken from another sales channel
        return $availabilityByScope[$scope] ?? [];
    }
}
