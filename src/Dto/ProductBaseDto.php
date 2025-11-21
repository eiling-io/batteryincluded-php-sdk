<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

class ProductBaseDto extends AbstractDto
{
    private ?string $id = null;

    private ?string $name = null;

    private ?string $description = null;

    private ?string $ordernumber = null;

    private ?string $manufacture = null;

    private ?string $manufactureNumber = null;

    private ?string $ean = null;

    private ?string $imageUrl = null;

    private ?string $shopUrl = null;

    private ?float $price = null;

    private ?int $instock = null;

    private ?float $rating = null;

    private array $categories = [];

    private ?ProductPropertyDto $properties = null;

    public function __construct(string $identifier, string $type = 'PRODUCT')
    {
        parent::__construct($identifier, $type);
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getOrdernumber(): ?string
    {
        return $this->ordernumber;
    }

    public function setOrdernumber(?string $ordernumber): void
    {
        $this->ordernumber = $ordernumber;
    }

    public function getManufacture(): ?string
    {
        return $this->manufacture;
    }

    public function setManufacture(?string $manufacture): void
    {
        $this->manufacture = $manufacture;
    }

    public function getManufactureNumber(): ?string
    {
        return $this->manufactureNumber;
    }

    public function setManufactureNumber(?string $manufactureNumber): void
    {
        $this->manufactureNumber = $manufactureNumber;
    }

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function setEan(?string $ean): void
    {
        $this->ean = $ean;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): void
    {
        $this->imageUrl = $imageUrl;
    }

    public function getShopUrl(): ?string
    {
        return $this->shopUrl;
    }

    public function setShopUrl(?string $shopUrl): void
    {
        $this->shopUrl = $shopUrl;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): void
    {
        $this->price = $price;
    }

    public function getInstock(): ?int
    {
        return $this->instock;
    }

    public function setInstock(?int $instock): void
    {
        $this->instock = $instock;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): void
    {
        $this->rating = $rating;
    }

    public function getCategories(): ?array
    {
        if (count(array_values(array_unique($this->categories))) === 0) {
            return null;
        }

        return array_values(array_unique($this->categories));
    }

    public function addCategory(CategoryDto $category): void
    {
        $this->categories = array_merge($this->categories, $category->jsonSerialize());
    }

    public function getProperties(): ?ProductPropertyDto
    {
        return $this->properties;
    }

    public function setProperties(?ProductPropertyDto $properties): void
    {
        $this->properties = $properties;
    }

    public function jsonSerialize(): array
    {
        $jsonDto = [
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
            'rating' => $this->getRating(),
            'categories' => $this->getCategories(),
            'properties' => $this->getProperties(),
        ];

        $jsonRaw = array_merge(
            parent::jsonSerialize(),
            ['_' . $this->getType() => array_filter($jsonDto, static fn ($value) => $value !== null)]
        );

        return $jsonRaw;
    }
}
