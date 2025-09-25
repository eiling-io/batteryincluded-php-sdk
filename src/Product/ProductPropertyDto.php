<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Product;

class ProductPropertyDto implements \JsonSerializable
{
    private array $properties = [];

    public function addProperty(string $option, string $value): self
    {
        $this->properties[$option][] = $value;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [$this->properties];
    }
}
