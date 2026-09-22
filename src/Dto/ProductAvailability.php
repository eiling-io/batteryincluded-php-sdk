<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

final class ProductAvailability extends AbstractAvailability
{
    public function __construct(
        string $market,
        private ?bool $active = null,
        private ?int $instock = null,
        private ?float $price = null,
        private array $categories = [],
    ) {
        parent::__construct($market);
    }

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function getInstock(): ?int
    {
        return $this->instock;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function getCategories(): array
    {
        return $this->categories;
    }

    public function toArray(): array
    {
        return [
            'active' => $this->active,
            'instock' => $this->instock,
            'price' => $this->price,
            'categories' => $this->categories === [] ? null : $this->categories,
        ];
    }
}
