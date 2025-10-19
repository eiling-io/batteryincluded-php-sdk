<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

class FacetRatingDto extends FacetDto
{
    private array $ratings = [];

    public function __construct(array $data, protected array $appliedFilterValues = [])
    {
        parent::__construct($data, $appliedFilterValues);
        foreach ($data['counts'] ?? [] as $count) {
            $this->ratings[$count['value']] = new FacetValueDto(
                $count,
                $appliedFilterValues[$this->fieldName] ?? []
            );
        }
    }

    public function getRatings()
    {
        return $this->ratings;
    }
}
