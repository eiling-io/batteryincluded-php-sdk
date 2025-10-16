<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

class FacetSelectDto extends FacetDto
{
    private array $values = [];

    public function __construct(array $data, protected array $appliedFilterValues = [])
    {
        parent::__construct($data, $this->appliedFilterValues);
        foreach ($data['counts'] ?? [] as $count) {
            $this->values[$count['value']] = new FacetValueDto(
                $count,
                $appliedFilterValues[$this->fieldName] ?? []
            );
        }
    }

    public function getValues()
    {
        return $this->values;
    }
}
