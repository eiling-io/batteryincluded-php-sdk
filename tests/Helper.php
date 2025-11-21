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
    /**
     * @return ProductBaseDto[]
     */
    public static function generateProducts(
        int $iterations,
        array $colours = ['Blau', 'Rosa', 'Gold', 'Schwarz'],
        array $storages = ['128GB', '256GB', '512GB'],
    ): array {
        $products = [];
        $id = 0;
        for ($i = 1; $i <= $iterations; $i++) {
            foreach ($colours as $color) {
                foreach ($storages as $storage) {
                    $id++;
                    $product = new ProductBaseDto((string) $id);
                    $product->setName('iPhone ' . $i . ' Pro ' . $color . ' - ' . $storage);
                    $product->setDescription(
                        'The latest iPhone with advanced features. Color: ' . $color . ', Storage: ' . $storage . '.'
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
                    $product->setShopUrl('https://www.apple.com/iphone-17-pro/');
                    $product->setProperties(
                        (new ProductPropertyDto())
                            ->addProperty('Farbe', $color)
                            ->addProperty('Speicherkapazität', $storage)
                            ->addProperty('Displaygröße', '6,1')
                    );
                    $product->addCategory(
                        (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode('iPhone')->addCategoryNode(
                            'iPhone ' . $i . ' Pro'
                        )
                    );
                    $product->addCategory(
                        (new CategoryDto())->addCategoryNode('Apple')->addCategoryNode('iPhone Pro ' . $color)
                    );
                    $products[] = $product;
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
            $blogs[] = $blog;
        }

        return $blogs;
    }
}
