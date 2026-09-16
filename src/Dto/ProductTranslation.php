<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

final class ProductTranslation
{
    public function __construct(
        private ?string $name = null,
        private ?string $description = null,
        private array $categories = [],
        private ?ProductPropertyDto $properties = null,
        private ?string $url = null,
    ) {
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function getProperties(): ?ProductPropertyDto
    {
        return $this->properties;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'categories' => $this->categories === [] ? null : $this->categories,
            'properties' => $this->properties,
            'url' => $this->url,
        ];
    }
}
