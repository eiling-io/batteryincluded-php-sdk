<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests;

/**
 * Preview images of the blog posts in the test data: a magazine-like cover with a topic icon, drawn as svg.
 *
 * The images are delivered as data uri like the product images (see DeviceImage).
 */
class BlogImage
{
    // gradient and accent per topic, the posts cycle through the topics
    private const TOPICS = [
        'battery' => ['from' => '#0f9b8e', 'to' => '#04b4e0', 'accent' => '#d9fbff'],
        'smartphone' => ['from' => '#3d5a80', 'to' => '#6b8fc7', 'accent' => '#dbe8ff'],
        'tablet' => ['from' => '#7b4fb0', 'to' => '#b07fe0', 'accent' => '#f0e3ff'],
        'laptop' => ['from' => '#2b2b2f', 'to' => '#55565e', 'accent' => '#e6e6ea'],
        'storage' => ['from' => '#c2603a', 'to' => '#eb9b5c', 'accent' => '#ffe9d6'],
        'display' => ['from' => '#1f6f8b', 'to' => '#3fb0c9', 'accent' => '#d8f6fc'],
        'colors' => ['from' => '#b85c7a', 'to' => '#e3a5b8', 'accent' => '#ffe3ec'],
        'accessories' => ['from' => '#4a5568', 'to' => '#8792a8', 'accent' => '#e7ebf3'],
        'sustainability' => ['from' => '#2f7d4f', 'to' => '#6cbf84', 'accent' => '#e0f7e7'],
        'news' => ['from' => '#9c7a3c', 'to' => '#d6b77c', 'accent' => '#fff3d9'],
    ];

    private const WIDTH = 600;

    private const HEIGHT = 400;

    public static function dataUri(int $number): string
    {
        return 'data:image/svg+xml;charset=utf-8,' . rawurlencode(self::svg($number));
    }

    public static function svg(int $number): string
    {
        $topic = array_keys(self::TOPICS)[($number - 1) % count(self::TOPICS)];
        $colors = self::TOPICS[$topic];
        $label = sprintf('Blog · %02d', $number);

        return sprintf(
            '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%2$d" viewBox="0 0 %1$d %2$d">'
            . '<defs>'
            . '<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="%3$s"/><stop offset="1" stop-color="%4$s"/></linearGradient>'
            . '<filter id="shadow" x="-20%%" y="-20%%" width="140%%" height="140%%"><feDropShadow dx="0" dy="10" stdDeviation="14" flood-opacity=".25"/></filter>'
            . '<pattern id="dots" width="14" height="14" patternUnits="userSpaceOnUse"><circle cx="2" cy="2" r="1.6" fill="#fff" fill-opacity=".35"/></pattern>'
            . '</defs>'
            . '<rect width="%1$d" height="%2$d" fill="url(#bg)"/>'
            // decorative shapes like on a magazine cover
            . '<circle cx="520" cy="60" r="150" fill="#fff" fill-opacity=".08"/>'
            . '<circle cx="70" cy="380" r="120" fill="#fff" fill-opacity=".06"/>'
            . '<rect x="406" y="252" width="156" height="112" fill="url(#dots)"/>'
            . '<rect x="40" y="40" width="70" height="6" rx="3" fill="%5$s"/>'
            // topic icon on a card
            . '<g filter="url(#shadow)"><rect x="210" y="85" width="180" height="180" rx="36" fill="#fff" fill-opacity=".96"/></g>'
            . '<g transform="translate(240 115)" fill="none" stroke="%3$s" stroke-width="7" stroke-linecap="round" stroke-linejoin="round">%6$s</g>'
            // label
            . '<rect x="40" y="330" width="%7$d" height="34" rx="17" fill="#fff" fill-opacity=".92"/>'
            . '<text x="%8$d" y="352" font-family="Helvetica, Arial, sans-serif" font-size="15" font-weight="700" fill="%3$s" text-anchor="middle">%9$s</text>'
            . '</svg>',
            self::WIDTH,
            self::HEIGHT,
            $colors['from'],
            $colors['to'],
            $colors['accent'],
            self::icon($topic),
            24 + mb_strlen($label) * 9,
            40 + (int) ((24 + mb_strlen($label) * 9) / 2),
            htmlspecialchars($label, ENT_XML1)
        );
    }

    /**
     * Line icons in a 120x120 box.
     */
    private static function icon(string $topic): string
    {
        return match ($topic) {
            'battery' => '<rect x="10" y="32" width="90" height="56" rx="10"/><path d="M100 50h10v20h-10"/><path d="M58 42l-14 20h18l-14 20"/>',
            'smartphone' => '<rect x="32" y="6" width="56" height="108" rx="12"/><path d="M52 20h16"/><circle cx="60" cy="96" r="3"/>',
            'tablet' => '<rect x="8" y="18" width="104" height="84" rx="10"/><circle cx="102" cy="60" r="2"/><rect x="34" y="36" width="20" height="20" rx="5"/><rect x="62" y="36" width="20" height="20" rx="5"/><rect x="34" y="64" width="20" height="20" rx="5"/><rect x="62" y="64" width="20" height="20" rx="5"/>',
            'laptop' => '<rect x="20" y="22" width="80" height="56" rx="6"/><path d="M6 92h108l-8 10H14z"/><path d="M52 92h16"/>',
            'storage' => '<rect x="26" y="26" width="68" height="68" rx="8"/><rect x="44" y="44" width="32" height="32" rx="4"/><path d="M40 10v16M60 10v16M80 10v16M40 94v16M60 94v16M80 94v16M10 40h16M10 60h16M10 80h16M94 40h16M94 60h16M94 80h16"/>',
            'display' => '<rect x="10" y="14" width="100" height="70" rx="8"/><path d="M44 106h32M60 84v22"/><path d="M28 70l20-20 14 12 26-24"/>',
            'colors' => '<path d="M60 10c-28 0-50 20-50 46 0 24 20 40 38 40 8 0 10-6 8-12-3-8 2-14 10-14h12c20 0 32-12 32-28 0-18-22-32-50-32z"/><circle cx="36" cy="50" r="6"/><circle cx="56" cy="32" r="6"/><circle cx="80" cy="40" r="6"/>',
            'accessories' => '<path d="M16 76V60a44 44 0 0 1 88 0v16"/><rect x="10" y="70" width="22" height="36" rx="8"/><rect x="88" y="70" width="22" height="36" rx="8"/>',
            'sustainability' => '<path d="M20 100C20 50 50 18 104 14c0 54-30 84-74 84"/><path d="M20 100c16-26 36-44 60-58"/>',
            default => '<path d="M60 8l13 32 34 3-26 22 8 33-29-18-29 18 8-33-26-22 34-3z"/>',
        };
    }
}
