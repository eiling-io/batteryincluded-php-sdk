<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

class FacetRangeDto extends FacetDto
{
    private float $min = 0.000;

    private float $max = 0.000;

    public function __construct(array $data, protected array $appliedFilterValues = [])
    {
        parent::__construct($data, $appliedFilterValues);
        $this->min = (float) ($data['stats']['min'] ?? 0.00);
        $this->max = (float) ($data['stats']['max'] ?? 0.00);
    }

    public function getMin(): float
    {
        return $this->min;
    }

    public function getSelectedMin(): float
    {
        if (isset($this->appliedFilterValues[$this->fieldName]['from'])) {
            return (float) $this->appliedFilterValues[$this->fieldName]['from'];
        }

        return $this->min;
    }

    public function getMax(): float
    {
        return $this->max;
    }

    public function getSelectedMax(): float
    {
        if (isset($this->appliedFilterValues[$this->fieldName]['till'])) {
            return (float) $this->appliedFilterValues[$this->fieldName]['till'];
        }

        return $this->max;
    }
}
