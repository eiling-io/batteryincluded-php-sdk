<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Product;

use JsonSerializable;

class CategoryDto implements JsonSerializable
{
    private array $categories;

    private string $lastCategoryTreeString = '';

    public function addCategoryNode(string $category): self
    {
        if (!empty($this->lastCategoryTreeString)) {
            $this->lastCategoryTreeString .= ' > ';
        }
        $this->lastCategoryTreeString .= str_replace('>', '', $category);

        $this->categories[] = $this->lastCategoryTreeString;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return $this->categories;
    }
}
