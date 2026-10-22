<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests;

/**
 * Product images of the test data: a phone, a tablet and a laptop drawn as svg in the colour of the product.
 *
 * The images are delivered as data uri, so they work without any hosting (ci, ddev, BatteryIncluded backend).
 */
class DeviceImage
{
    // frame, light and dark shade of the wallpaper per colour of the test data
    private const COLORS = [
        'Blau' => ['frame' => '#3d5a80', 'light' => '#a8c8f0', 'dark' => '#1f3b63'],
        'Rosa' => ['frame' => '#e3a5b8', 'light' => '#fbd9e3', 'dark' => '#b85c7a'],
        'Gold' => ['frame' => '#d6b77c', 'light' => '#f6e7c4', 'dark' => '#9c7a3c'],
        'Schwarz' => ['frame' => '#2b2b2f', 'light' => '#6b6b75', 'dark' => '#111114'],
    ];

    private const WIDTH = 600;

    private const HEIGHT = 400;

    public static function dataUri(string $device, string $color, string $label): string
    {
        return 'data:image/svg+xml;charset=utf-8,' . rawurlencode(self::svg($device, $color, $label));
    }

    public static function svg(string $device, string $color, string $label): string
    {
        $colors = self::COLORS[$color] ?? self::COLORS['Schwarz'];

        $body = match ($device) {
            'Tablet' => self::tablet($colors),
            'Laptop' => self::laptop($colors),
            default => self::phone($colors),
        };

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d">'
            . '<defs>'
            . '<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#f7f8fa"/><stop offset="1" stop-color="%3$s" stop-opacity=".35"/></linearGradient>'
            . '<linearGradient id="screen" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="%3$s"/><stop offset="1" stop-color="%4$s"/></linearGradient>'
            . '<linearGradient id="shine" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#fff" stop-opacity=".25"/><stop offset=".5" stop-color="#fff" stop-opacity="0"/></linearGradient>'
            . '<filter id="shadow" x="-20%%" y="-20%%" width="140%%" height="140%%"><feDropShadow dx="0" dy="10" stdDeviation="12" flood-opacity=".25"/></filter>'
            . '</defs>'
            . '<rect width="%1$d" height="%2$d" fill="url(#bg)"/>'
            . '%5$s'
            . '<rect x="20" y="350" width="%6$d" height="30" rx="15" fill="#fff" fill-opacity=".85"/>'
            . '<text x="%7$d" y="370" font-family="Helvetica, Arial, sans-serif" font-size="14" font-weight="600" fill="#333" text-anchor="middle">%8$s</text>'
            . '</svg>',
            self::WIDTH,
            self::HEIGHT,
            $colors['light'],
            $colors['dark'],
            $body,
            20 + mb_strlen($label) * 9,
            20 + (int) ((20 + mb_strlen($label) * 9) / 2),
            htmlspecialchars($label, ENT_XML1)
        );
    }

    /**
     * Phone in portrait with dynamic island and side buttons.
     */
    private static function phone(array $colors): string
    {
        return sprintf(
            '<g filter="url(#shadow)">'
            . '<rect x="227" y="88" width="4" height="34" rx="2" fill="%1$s"/>'
            . '<rect x="227" y="132" width="4" height="34" rx="2" fill="%1$s"/>'
            . '<rect x="369" y="110" width="4" height="56" rx="2" fill="%1$s"/>'
            . '<rect x="230" y="40" width="140" height="290" rx="28" fill="%1$s"/>'
            . '<rect x="238" y="48" width="124" height="274" rx="21" fill="url(#screen)"/>'
            . '<rect x="238" y="48" width="124" height="274" rx="21" fill="url(#shine)"/>'
            . '<rect x="280" y="58" width="40" height="12" rx="6" fill="#0b0b0d"/>'
            . '<circle cx="300" cy="185" r="26" fill="#fff" fill-opacity=".18"/>'
            . '<rect x="276" y="306" width="48" height="4" rx="2" fill="#fff" fill-opacity=".6"/>'
            . '</g>',
            $colors['frame']
        );
    }

    /**
     * Tablet in landscape with front camera.
     */
    private static function tablet(array $colors): string
    {
        return sprintf(
            '<g filter="url(#shadow)">'
            . '<rect x="190" y="57" width="40" height="4" rx="2" fill="%1$s"/>'
            . '<rect x="120" y="60" width="360" height="250" rx="22" fill="%1$s"/>'
            . '<rect x="132" y="72" width="336" height="226" rx="12" fill="url(#screen)"/>'
            . '<rect x="132" y="72" width="336" height="226" rx="12" fill="url(#shine)"/>'
            . '<circle cx="300" cy="66" r="2.5" fill="#0b0b0d"/>'
            . '<rect x="160" y="100" width="70" height="70" rx="16" fill="#fff" fill-opacity=".2"/>'
            . '<rect x="245" y="100" width="70" height="70" rx="16" fill="#fff" fill-opacity=".14"/>'
            . '<rect x="160" y="185" width="155" height="70" rx="16" fill="#fff" fill-opacity=".1"/>'
            . '</g>',
            $colors['frame']
        );
    }

    /**
     * Opened laptop with notch, keyboard deck and trackpad edge.
     */
    private static function laptop(array $colors): string
    {
        return sprintf(
            '<g filter="url(#shadow)">'
            . '<rect x="140" y="50" width="320" height="215" rx="14" fill="%1$s"/>'
            . '<rect x="150" y="60" width="300" height="195" rx="6" fill="url(#screen)"/>'
            . '<rect x="150" y="60" width="300" height="195" rx="6" fill="url(#shine)"/>'
            . '<path d="M285 60h30v6a4 4 0 0 1-4 4h-22a4 4 0 0 1-4-4z" fill="#0b0b0d"/>'
            . '<rect x="170" y="85" width="120" height="14" rx="7" fill="#fff" fill-opacity=".25"/>'
            . '<rect x="170" y="110" width="80" height="10" rx="5" fill="#fff" fill-opacity=".15"/>'
            . '<path d="M100 268h400l-14 22a10 10 0 0 1-8.5 5h-355a10 10 0 0 1-8.5-5z" fill="%1$s"/>'
            . '<rect x="100" y="265" width="400" height="6" rx="3" fill="%1$s"/>'
            . '<path d="M100 268h400l-14 22a10 10 0 0 1-8.5 5h-355a10 10 0 0 1-8.5-5z" fill="#000" fill-opacity=".12"/>'
            . '<rect x="270" y="268" width="60" height="5" rx="2.5" fill="#000" fill-opacity=".2"/>'
            . '</g>',
            $colors['frame']
        );
    }
}
