<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

class ProductBaseDto extends AbstractAvailabilityDto
{
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

    private ?bool $active = null;

    private ?float $rating = null;

    private array $categories = [];

    private ?ProductPropertyDto $properties = null;

    public function __construct(string $identifier, string $type = 'PRODUCT')
    {
        parent::__construct($identifier, $type);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getOrdernumber(): ?string
    {
        return $this->ordernumber;
    }

    public function setOrdernumber(?string $ordernumber): static
    {
        $this->ordernumber = $ordernumber;

        return $this;
    }

    public function getManufacture(): ?string
    {
        return $this->manufacture;
    }

    public function setManufacture(?string $manufacture): static
    {
        $this->manufacture = $manufacture;

        return $this;
    }

    public function getManufactureNumber(): ?string
    {
        return $this->manufactureNumber;
    }

    public function setManufactureNumber(?string $manufactureNumber): static
    {
        $this->manufactureNumber = $manufactureNumber;

        return $this;
    }

    public function getEan(): ?string
    {
        return $this->ean;
    }

    public function setEan(?string $ean): static
    {
        $this->ean = $ean;

        return $this;
    }

    public function getImageUrl(): ?string
    {
        return $this->imageUrl;
    }

    public function setImageUrl(?string $imageUrl): static
    {
        $this->imageUrl = $imageUrl;

        return $this;
    }

    public function getShopUrl(): ?string
    {
        return $this->shopUrl;
    }

    public function setShopUrl(?string $shopUrl): static
    {
        $this->shopUrl = $shopUrl;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(?float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getInstock(): ?int
    {
        return $this->instock;
    }

    public function setInstock(?int $instock): static
    {
        $this->instock = $instock;

        return $this;
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(?bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getRating(): ?float
    {
        return $this->rating;
    }

    public function setRating(?float $rating): static
    {
        $this->rating = $rating;

        return $this;
    }

    public function getCategories(): ?array
    {
        if (count(array_values(array_unique($this->categories))) === 0) {
            return null;
        }

        return array_values(array_unique($this->categories));
    }

    public function addCategory(CategoryDto $category): static
    {
        $this->categories = array_merge($this->categories, $category->jsonSerialize());

        return $this;
    }

    public function getProperties(): ?ProductPropertyDto
    {
        return $this->properties;
    }

    public function setProperties(?ProductPropertyDto $properties): static
    {
        $this->properties = $properties;

        return $this;
    }

    protected function getTranslatableFields(): ?array
    {
        return [
            'name',
            'description',
            'ordernumber',
            'manufacture',
            'manufactureNumber',
            'ean',
            'imageUrl',
            'shopUrl',
            'price',
            'instock',
            'rating',
            'categories',
            'properties',
        ];
    }

    public function setPropertiesTranslation(string $locale, ProductPropertyDto $properties): static
    {
        return $this->addTranslation($locale, 'properties', $properties);
    }

    public function addPropertyTranslation(string $locale, string $option, string $value): static
    {
        $properties = $this->getTranslation($locale)['properties'] ?? new ProductPropertyDto();

        return $this->setPropertiesTranslation($locale, $properties->addProperty($option, $value));
    }

    public function addCategoryTranslation(string $locale, CategoryDto $category): static
    {
        return $this->addTranslation($locale, 'categories', array_values(array_unique(array_merge(
            $this->getTranslation($locale)['categories'] ?? [],
            $category->jsonSerialize()
        ))));
    }

    protected function getAvailabilityFields(): ?array
    {
        return ['active', 'price', 'instock'];
    }

    /**
     * Null values are not exported, the value of the payload applies to them.
     */
    public function setAvailability(string $scope, ?bool $active = null, ?float $price = null, ?int $instock = null): static
    {
        foreach (['active' => $active, 'price' => $price, 'instock' => $instock] as $field => $value) {
            if ($value !== null) {
                $this->addAvailability($scope, $field, $value);
            }
        }

        return $this;
    }

    public function jsonSerialize(): array
    {
        $jsonDto = [
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
            'active' => $this->getActive(),
            'rating' => $this->getRating(),
            'categories' => $this->getCategories(),
            'properties' => $this->getProperties(),
        ];

        $jsonRaw = array_merge(
            parent::jsonSerialize(),
            ['_' . $this->getType() => $this->filterJsonValues($jsonDto)]
        );

        return $jsonRaw;
    }
}
