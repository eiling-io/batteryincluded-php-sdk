<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

class ProductPropertyDto implements \JsonSerializable
{
    private array $properties = [];

    public function addProperty(string $option, string $value): self
    {
        $this->properties[str_replace('.', '', $option)][] = $value;

        return $this;
    }

    public function jsonSerialize(): array
    {
        return [$this->properties];
    }
}
