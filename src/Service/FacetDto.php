<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Service;

class FacetDto
{
    private array $stats;

    private string $fieldLabel;

    private string $fieldUnit;

    private string $fieldName;

    private bool $checked = false;

    private array $values = [];

    public function __construct(array $data, private array $appliedFilterValues = [])
    {
        $this->fieldLabel = $data['field_label'] ?? '';
        $this->fieldUnit = $data['field_unit'] ?? '';
        $this->fieldName = $data['field_name'] ?? '';
        $this->stats = $data['stats'] ?? [];
        if (in_array($this->fieldName, array_keys($this->appliedFilterValues), true)) {
            $this->checked = true;
        }

        foreach ($data['counts'] as $count) {
            $this->values[$count['value']] = new FacetValueDto($count, $appliedFilterValues[$this->fieldName] ?? []);
        }
    }

    public function getValues()
    {
        return $this->values;
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
