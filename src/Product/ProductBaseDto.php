<?php
declare(strict_types=1);

namespace BatteryIncludedSdk\Product;

use JsonSerializable;

class ProductBaseDto implements JsonSerializable
{
    private string $id;
    
    private string $name;

    private string $description;

    private string $ordernumber;

    private string $manufacture;

    private string $manufactureNumber;

    private string $ean;

    private string $imageUrl;

    private string $shopUrl;

    private float $price;

    private int $instock;

    private array $categories = [];

    private ProductPropertyDto $properties;

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getOrdernumber(): string
    {
        return $this->ordernumber;
    }

    public function setOrdernumber(string $ordernumber): void
    {
        $this->ordernumber = $ordernumber;
    }

    public function getManufacture(): string
    {
        return $this->manufacture;
    }

    public function setManufacture(string $manufacture): void
    {
        $this->manufacture = $manufacture;
    }

    public function getManufactureNumber(): string
    {
        return $this->manufactureNumber;
    }

    public function setManufactureNumber(string $manufactureNumber): void
    {
        $this->manufactureNumber = $manufactureNumber;
    }

    public function getEan(): string
    {
        return $this->ean;
    }

    public function setEan(string $ean): void
    {
        $this->ean = $ean;
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function getShopUrl(): string
    {
        return $this->shopUrl;
    }

    public function setShopUrl(string $shopUrl): void
    {
        $this->shopUrl = $shopUrl;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getInstock(): int
    {
        return $this->instock;
    }

    public function setInstock(int $instock): void
    {
        $this->instock = $instock;
    }

    public function getCategories(): array
    {
        return array_values(array_unique($this->categories));
    }

    /**
     * please add for each category tree element one CategoryDto
     * @param CategoryDto $category
     * @return void
     */
    public function addCategory(CategoryDto $category): void
    {
        $this->categories = array_merge($this->categories, $category->jsonSerialize());
    }

    public function getProperties(): ProductPropertyDto
    {
        return $this->properties;
    }

    public function setProperties(ProductPropertyDto $properties): void
    {
        $this->properties = $properties;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'ordernumber' => $this->getOrdernumber(),
            'manufacture' => $this->getManufacture(),
            'manufactureNumber' => $this->getManufactureNumber(),
            'ean' => $this->getEan(),
            'imageUrl' => $this->getImageUrl(),
            'shopUrl' => $this->getShopUrl(),
            'price' => $this->getPrice(),
            'instock' => $this->getInstock(),
            'categories' => $this->getCategories(),
            'properties' => $this->getProperties(),
        ];
    }
}