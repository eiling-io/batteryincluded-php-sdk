<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

class FacetDto
{
    protected array $stats;

    protected string $fieldLabel;

    protected string $fieldUnit;

    protected string $fieldName;

    protected string $type;

    private bool $checked = false;

    public function __construct(array $data, protected array $appliedFilterValues = [])
    {
        $this->fieldLabel = $data['field_label'] ?? '';
        $this->fieldUnit = $data['field_unit'] ?? '';
        $this->fieldName = $data['field_name'] ?? '';
        $this->stats = $data['stats'] ?? [];
        $this->type = $data['type'] ?? '';
        if (in_array($this->fieldName, array_keys($this->appliedFilterValues), true)) {
            $this->checked = true;
        }
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
