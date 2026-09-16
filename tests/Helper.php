<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\BlogBaseDto;
use BatteryIncludedSdk\Dto\BlogTranslation;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductAvailability;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;
use BatteryIncludedSdk\Dto\ProductTranslation;

class Helper
{
    /** German colour (the default locale used below) => English translation, demonstrates addTranslation() */
    private const COLOUR_EN = [
        'Blau' => 'Blue',
        'Rosa' => 'Pink',
        'Gold' => 'Gold',
        'Schwarz' => 'Black',
    ];

    /**
     * @return ProductBaseDto[]
     */
    public static function generateProducts(
        int $iterations,
        array $devices = ['iPhone', 'iPad', 'MacBook'],
        array $colours = ['Blau', 'Rosa', 'Gold', 'Schwarz'],
        array $storages = ['128GB', '256GB', '512GB'],
    ): array {
        $products = [];
        $id = 0;
        for ($i = 1; $i <= $iterations; $i++) {
            foreach ($devices as $device) {
                foreach ($colours as $color) {
                    foreach ($storages as $storage) {
                        $id++;
                        $colorEn = self::COLOUR_EN[$color] ?? $color;

                        $product = new ProductBaseDto((string) $id);
                        $product->setName($device . ' ' . $i . ' Pro ' . $color . ' - ' . $storage);
                        $product->setDescription(
                            'Das neueste ' . $device . ' mit fortschrittlichen Funktionen. Farbe: ' . $color . ', Speicherkapazität: ' . $storage . '.'
                        );
                        $product->setId((string) $id);
                        $product->setOrdernumber('AP-00' . $i . '-' . $color . '-' . $storage);
                        $product->setPrice(1000 + $id);
                        $product->setInstock(rand(0, 50));
                        $product->setRating((float) (mt_rand(1, 10) / 2));
                        $product->setManufacture('Apple');
                        $product->setManufactureNumber('A' . $i . '-' . $color . '-' . $storage);
                        $product->setEan('195950639292');
                        $product->setImageUrl(
                            'https://dummyimage.com/600x400/bbb/fff.png&text=' . $i . '-' . $color . '-' . $storage
                        );
                        $product->setShopUrl('https://www.apple.com/' . $device . '-' . $i . '-pro/');

                        // property keys are not locale-specific - BatteryIncluded maps a key like
                        // "Color" to its displayed label ("Farbe" in German UI, "Color" in English UI)
                        // on its end, so the same keys are used across every locale below; only values
                        // that are actual language content (e.g. the colour name) get translated.
                        $properties = [
                            'Device' => $device,
                            'Color' => $color,
                            'Storage' => $storage,
                            'Display size' => '6,1',
                        ];
                        $product->setProperties(self::buildProperties($properties));
                        $product->addCategory(
                            (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device)->addCategoryNode($device . ' ' . $i . ' Pro')
                        );
                        $product->addCategory(
                            (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device . ' Pro ' . $color)
                        );

                        // en translation alongside the de content above, synced in the same document (see
                        // addTranslation() in ProductBaseDto / the "Localization (i18n)" section of the README)
                        $product->addTranslation(new ProductTranslation(
                            locale: 'en',
                            name: $device . ' ' . $i . ' Pro ' . $colorEn . ' - ' . $storage,
                            description: 'The latest ' . $device . ' with advanced features. Color: ' . $colorEn . ', Storage: ' . $storage . '.',
                            categories: array_values(array_unique(array_merge(
                                (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device)->addCategoryNode($device . ' ' . $i . ' Pro')->jsonSerialize(),
                                (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device . ' Pro ' . $colorEn)->jsonSerialize(),
                            ))),
                            properties: self::buildProperties(array_merge($properties, [
                                'Color' => $colorEn,
                                'Display size' => '6.1', // en uses a decimal point, de a decimal comma
                            ])),
                        ));

                        // per-market availability alongside the translations above, synced in the same
                        // document (see addAvailability() in ProductBaseDto / the "Market availability"
                        // section of the README) - de/at/ch each get their own stock and price here, and
                        // "ch" occasionally isn't sold at all to demonstrate the inactive case.
                        $atInstock = max(0, $product->getInstock() - rand(0, 10));
                        $chActive = $product->getInstock() > 0;
                        $product->addAvailability(new ProductAvailability(
                            market: 'de',
                            active: true,
                            instock: $product->getInstock(),
                            price: $product->getPrice(),
                        ));
                        $product->addAvailability(new ProductAvailability(
                            market: 'at',
                            active: $atInstock > 0,
                            instock: $atInstock,
                            price: $product->getPrice() + 20,
                        ));
                        $product->addAvailability(new ProductAvailability(
                            market: 'ch',
                            active: $chActive,
                            instock: $chActive ? max(0, $product->getInstock() - rand(0, 15)) : 0,
                            price: $product->getPrice() + 50,
                        ));

                        $products[] = $product;
                    }
                }
            }
        }

        return $products;
    }

    /**
     * @param array<string, string> $values
     */
    private static function buildProperties(array $values): ProductPropertyDto
    {
        $properties = new ProductPropertyDto();
        foreach ($values as $label => $value) {
            $properties->addProperty($label, $value);
        }

        return $properties;
    }

    public static function getApiClient(): ApiClient
    {
        return new ApiClient(
            new CurlHttpClient(),
            'https://api.batteryincluded.io/api/v1/collections/',
            getenv('COLLECTION'),
            getenv('APIKEY')
        );
    }

    /**
     * @return BlogBaseDto[]
     */
    public static function generateBlogs(int $int): array
    {
        $blogs = [];
        for ($i = 1; $i <= $int; $i++) {
            $blog = new BlogBaseDto((string) $i, 'BLOG');
            $blog->setTitle('Blogbeitrag ' . $i);
            $blog->setDescription('Dies ist der Inhalt von Blogbeitrag Nummer ' . $i . '.');
            $blog->setAuthor('Autor ' . $i);
            $blog->setPreviewImage('https://dummyimage.com/600x400/bbb/fff.png&text=Blog ' . $i);
            $blog->setPublishedAt((new \DateTime())->modify('-' . (30 - $i) . ' days')->format('Y-m-d'));

            // en translation alongside the de content above, synced in the same document
            $blog->addTranslation(new BlogTranslation(
                locale: 'en',
                title: 'Blog Post ' . $i,
                description: 'This is the content of blog post number ' . $i . '.',
            ));

            $blogs[] = $blog;
        }

        return $blogs;
    }
}
