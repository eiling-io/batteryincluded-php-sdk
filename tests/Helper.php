<?php

declare(strict_types=1);

namespace BatteryIncludedSdkTests;

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Dto\BlogBaseDto;
use BatteryIncludedSdk\Dto\CategoryDto;
use BatteryIncludedSdk\Dto\ProductBaseDto;
use BatteryIncludedSdk\Dto\ProductPropertyDto;

class Helper
{
    private const COLORS = [
        'de' => ['Blau' => 'Blau', 'Rosa' => 'Rosa', 'Gold' => 'Gold', 'Schwarz' => 'Schwarz'],
        'en' => ['Blau' => 'Blue', 'Rosa' => 'Pink', 'Gold' => 'Gold', 'Schwarz' => 'Black'],
    ];

    private const TEXTS = [
        'de' => [
            'description' => 'Das neueste %s mit vielen Funktionen. Farbe: %s, Speicherkapazität: %s.',
            'shopUrl' => 'https://www.apple.com/de/%s-%d-pro/',
            'decimalSeparator' => ',',
        ],
        'en' => [
            'description' => 'The latest %s with advanced features. Color: %s, Storage: %s.',
            'shopUrl' => 'https://www.apple.com/en/%s-%d-pro/',
            'decimalSeparator' => '.',
        ],
    ];

    // display sizes in inch of the small and the large model of each device, the generations alternate between both
    private const DISPLAY_SIZES = [
        'iPhone' => [6.3, 6.9],
        'iPad' => [11.0, 13.0],
        'MacBook' => [14.2, 16.2],
    ];

    /**
     * @return ProductBaseDto[]
     */
    public static function generateProducts(
        int $iterations,
        array $devices = ['iPhone', 'iPad', 'MacBook'],
        array $colours = ['Blau', 'Rosa', 'Gold', 'Schwarz'],
        array $storages = ['128GB', '256GB', '512GB'],
        bool $withTranslations = true,
    ): array {
        $products = [];
        $id = 0;
        for ($i = 1; $i <= $iterations; $i++) {
            foreach ($devices as $device) {
                foreach ($colours as $color) {
                    foreach ($storages as $storage) {
                        $id++;
                        $product = new ProductBaseDto((string) $id);
                        $product->setName($device . ' ' . $i . ' Pro ' . $color . ' - ' . $storage);
                        $product->setDescription(
                            'Das neueste ' . $device . ' mit vielen Funktionen. Farbe: ' . $color . ', Speicherkapazität: ' . $storage . '.'
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
                        // property names are always english, the labels are translated in the backend
                        $product->setProperties(
                            (new ProductPropertyDto())
                                ->addProperty('Device', $device)
                                ->addProperty('Color', $color)
                                ->addProperty('Storage', $storage)
                                ->addProperty('Display size', self::displaySize($device, $i, 'de'))
                        );
                        $product->addCategory(
                            (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device)->addCategoryNode($device . ' ' . $i . ' Pro')
                        );
                        $product->addCategory(
                            (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device . ' Pro ' . $color)
                        );
                        if ($withTranslations) {
                            // every locale is exported to the _PRODUCT_i18n root node, the regular fields above are the fallback
                            foreach (array_keys(self::TEXTS) as $locale) {
                                self::addTranslations($product, $locale, $i, $device, $color, $storage);
                            }
                        }
                        $products[] = $product;
                    }
                }
            }
        }

        return $products;
    }

    private static function addTranslations(
        ProductBaseDto $product,
        string $locale,
        int $i,
        string $device,
        string $color,
        string $storage,
    ): void {
        $texts = self::TEXTS[$locale];
        $translatedColor = self::COLORS[$locale][$color] ?? $color;

        $product
            ->addTranslation($locale, 'name', $device . ' ' . $i . ' Pro ' . $translatedColor . ' - ' . $storage)
            ->addTranslation($locale, 'description', sprintf($texts['description'], $device, $translatedColor, $storage))
            ->addTranslation($locale, 'shopUrl', sprintf($texts['shopUrl'], $device, $i))
            ->setPropertiesTranslation(
                $locale,
                (new ProductPropertyDto())
                    ->addProperty('Device', $device)
                    ->addProperty('Color', $translatedColor)
                    ->addProperty('Storage', $storage)
                    ->addProperty('Display size', self::displaySize($device, $i, $locale))
            )
            ->addCategoryTranslation(
                $locale,
                (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device)->addCategoryNode($device . ' ' . $i . ' Pro')
            )
            ->addCategoryTranslation(
                $locale,
                (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode($device . ' Pro ' . $translatedColor)
            );
    }

    private static function displaySize(string $device, int $i, string $locale): string
    {
        $size = self::DISPLAY_SIZES[$device][$i % 2] ?? self::DISPLAY_SIZES['iPhone'][$i % 2];
        $decimals = floor($size) === $size ? 0 : 1;

        return number_format($size, $decimals, self::TEXTS[$locale]['decimalSeparator'], '');
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
            $blog
                ->addTranslation('de', 'title', 'Blogbeitrag ' . $i)
                ->addTranslation('de', 'description', 'Das ist der Inhalt von Blogbeitrag Nummer ' . $i . '.')
                ->addTranslation('en', 'title', 'Blog Post ' . $i)
                ->addTranslation('en', 'description', 'This is the content of blog post number ' . $i . '.');
            $blogs[] = $blog;
        }

        return $blogs;
    }
}
