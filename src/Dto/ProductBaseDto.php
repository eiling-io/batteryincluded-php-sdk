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

    /** @var array<string, ProductTranslation> additional locales beyond the one written by the flat setters */
    private array $translations = [];

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

    /**
     * Adds (or replaces) a translation for an additional locale. The locale reached by the flat
     * setters (setName(), setDescription(), addCategory(), setProperties()) is controlled via locale().
     */
    public function addTranslation(string $locale, ProductTranslation $translation): void
    {
        $this->translations[$locale] = $translation;
    }

    public function jsonSerialize(): array
    {
        $translations = [$this->activeLocale() => new ProductTranslation(
            $this->getName(),
            $this->getDescription(),
            $this->getCategories() ?? [],
            $this->getProperties(),
        )];

        foreach ($this->translations as $locale => $translation) {
            $translations[$locale] = $translation;
        }

        $i18n = array_map(
            fn (ProductTranslation $translation) => [
                '_' . $this->getType() => $this->filterJsonValues($this->withUrlFallback($translation)),
            ],
            $translations
        );

        $jsonDto = [
            'id' => $this->getId(),
            'ordernumber' => $this->getOrdernumber(),
            'manufacture' => $this->getManufacture(),
            'manufactureNumber' => $this->getManufactureNumber(),
            'ean' => $this->getEan(),
            'imageUrl' => $this->getImageUrl(),
            'shopUrl' => $this->getShopUrl(),
            'price' => $this->getPrice(),
            'instock' => $this->getInstock(),
            'rating' => $this->getRating(),
        ];

        return array_merge(
            parent::jsonSerialize(),
            [
                '_i18n' => $i18n,
                '_' . $this->getType() => $this->filterJsonValues($jsonDto),
            ]
        );
    }

    /**
     * The product URL is often identical across locales, so a translation that doesn't set its own
     * falls back to shopUrl() rather than forcing every addTranslation() call to repeat it.
     */
    private function withUrlFallback(ProductTranslation $translation): array
    {
        $payload = $translation->toArray();
        $payload['url'] ??= $this->shopUrl;

        return $payload;
    }
}
