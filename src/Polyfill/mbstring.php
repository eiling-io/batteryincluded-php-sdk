<?php

declare(strict_types=1);

/*
 * mb_trim() is available since php 8.4, the cs fixer (mb_str_functions) replaces trim() with it.
 * For older php versions the sdk provides it, the native function is used whenever it exists.
 */

if (!function_exists('mb_trim')) {
    function mb_trim(string $string, ?string $characters = null, ?string $encoding = null): string
    {
        if ($encoding !== null && $encoding !== 'UTF-8') {
            $string = mb_convert_encoding($string, 'UTF-8', $encoding);
        }

        // whitespace characters which are trimmed by the native mb_trim() by default
        $pattern = $characters === null
            ? '\s\x{0000}\x{00A0}\x{1680}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}\x{0085}\x{180E}'
            : preg_quote($characters, '/');

        $trimmed = (string) preg_replace('/^[' . $pattern . ']+|[' . $pattern . ']+$/u', '', $string);

        if ($encoding !== null && $encoding !== 'UTF-8') {
            return mb_convert_encoding($trimmed, $encoding, 'UTF-8');
        }

        return $trimmed;
    }
}
