<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\BlogBaseDto;
use BatteryIncludedSdk\Dto\BlogTranslation;
use BatteryIncludedSdk\Dto\CategoryDto;
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
                            'The latest ' . $device . ' with advanced features. Color: ' . $color . ', Storage: ' . $storage . '.'
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
                        $product->setProperties(
                            (new ProductPropertyDto())
                                ->addProperty('Gerät', $device)
                                ->addProperty('Farbe', $color)
                                ->addProperty('Speicherkapazität', $storage)
                                ->addProperty('Displaygröße', '6,1')
                        );
                        $product->addCategory(
                            (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device)->addCategoryNode($device . ' ' . $i . ' Pro')
                        );
                        $product->addCategory(
                            (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device . ' Pro ' . $color)
                        );

                        // en translation alongside the de content above, synced in the same document (see
                        // addTranslation() in ProductBaseDto / the "Localization (i18n)" section of the README)
                        $product->addTranslation('en', new ProductTranslation(
                            name: $device . ' ' . $i . ' Pro ' . $colorEn . ' - ' . $storage,
                            description: 'The latest ' . $device . ' with advanced features. Color: ' . $colorEn . ', Storage: ' . $storage . '.',
                            categories: array_values(array_unique(array_merge(
                                (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device)->addCategoryNode($device . ' ' . $i . ' Pro')->jsonSerialize(),
                                (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device . ' Pro ' . $colorEn)->jsonSerialize(),
                            ))),
                            properties: (new ProductPropertyDto())
                                ->addProperty('Device', $device)
                                ->addProperty('Colour', $colorEn)
                                ->addProperty('Storage', $storage)
                                ->addProperty('Display size', '6.1'),
                        ));

                        $products[] = $product;
                    }
                }
            }
        }

        return $products;
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
            $blog->setTitle('Blog Post ' . $i);
            $blog->setDescription('This is the content of blog post number ' . $i . '.');
            $blog->setAuthor('Author ' . $i);
            $blog->setPreviewImage('https://dummyimage.com/600x400/bbb/fff.png&text=Blog ' . $i);
            $blog->setPublishedAt((new \DateTime())->modify('-' . (30 - $i) . ' days')->format('Y-m-d'));

            // en translation alongside the de content above, synced in the same document
            $blog->addTranslation('en', new BlogTranslation(
                title: 'Blog Post ' . $i . ' (EN)',
                description: 'This is the English content of blog post number ' . $i . '.',
            ));

            $blogs[] = $blog;
        }

        return $blogs;
    }
}
