<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

class FacetValueDto
{
    private bool $checked = false;

    private string $name;

    private int $count;

    public function __construct(array $data, private array $appliedFilterValues = [])
    {
        $this->name = $data['value'] ?? '';
        $this->count = (int) ($data['count'] ?? 0);
        if (in_array($this->name, $this->appliedFilterValues, true)) {
            $this->checked = true;
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function isChecked(): bool
    {
        return $this->checked;
    }
}
