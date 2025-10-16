<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

class FacetDto
{
    private array $stats;

    private string $fieldLabel;

    private string $fieldUnit;

    private string $fieldName;

    private string $type;

    private bool $checked = false;

    private array $values = [];

    private float $min = 0.000;

    private float $max = 0.000;

    public function __construct(array $data, private array $appliedFilterValues = [])
    {
        $this->fieldLabel = $data['field_label'] ?? '';
        $this->fieldUnit = $data['field_unit'] ?? '';
        $this->fieldName = $data['field_name'] ?? '';
        $this->stats = $data['stats'] ?? [];
        $this->type = $data['type'] ?? 'select';
        if (in_array($this->fieldName, array_keys($this->appliedFilterValues), true)) {
            $this->checked = true;
        }

        if ($this->type === 'select') {
            foreach ($data['counts'] ?? [] as $count) {
                $this->values[$count['value']] = new FacetValueDto(
                    $count,
                    $appliedFilterValues[$this->fieldName] ?? []
                );
            }
        }

        if ($this->type === 'range') {
            $this->min = (float) ($data['stats']['min'] ?? 0.00);
            $this->max = (float) ($data['stats']['max'] ?? 0.00);
        }
    }

    public function getValues()
    {
        if ($this->getType() !== 'select') {
            throw new \InvalidArgumentException('The Facet Type is not "select". Only select elements have values.');
        }

        return $this->values;
    }

    public function getMin(): float
    {
        if ($this->getType() !== 'range') {
            throw new \InvalidArgumentException('The Facet Type is not "range". Only range elements have a max value.');
        }

        return $this->min;
    }

    public function getSelectedMin(): float
    {
        if ($this->getType() !== 'range') {
            throw new \InvalidArgumentException('The Facet Type is not "range". Only range elements have a max value.');
        }
        if (isset($this->appliedFilterValues[$this->fieldName]['from'])) {
            return (float) $this->appliedFilterValues[$this->fieldName]['from'];
        }

        return $this->min;
    }

    public function getMax(): float
    {
        if ($this->getType() !== 'range') {
            throw new \InvalidArgumentException('The Facet Type is not "range". Only range elements have a max value.');
        }

        return $this->max;
    }

    public function getSelectedMax(): float
    {
        if ($this->getType() !== 'range') {
            throw new \InvalidArgumentException('The Facet Type is not "range". Only range elements have a max value.');
        }
        if (isset($this->appliedFilterValues[$this->fieldName]['till'])) {
            return (float) $this->appliedFilterValues[$this->fieldName]['till'];
        }

        return $this->max;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getStats(): array
    {
        return $this->stats;
    }

    public function getFieldLabel(): string
    {
        if ($this->fieldLabel !== '') {
            return $this->fieldLabel;
        }

        return $this->fieldName;
    }

    public function getFieldUnit(): string
    {
        return $this->fieldUnit;
    }

    public function getFieldName(): string
    {
        return $this->fieldName;
    }

    public function isChecked(): bool
    {
        return $this->checked;
    }

    public function setChecked(bool $checked): void
    {
        $this->checked = $checked;
    }
}
