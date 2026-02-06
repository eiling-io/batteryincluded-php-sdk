<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Suggest;

class SuggestSearchStruct
{
    protected array $filters = [];

    protected string $query = '';

    public function setQuery(string $query): void
    {
        $this->query = $query;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function addFilter(string $key, string $value): void
    {
        $this->filters[$key][] = $value;
    }

    public function addFilters(array $filters): void
    {
        foreach ($filters as $key => $values) {
            foreach ($values as $arrayKey => $value) {
                $this->filters[$key][$arrayKey] = $value;
            }
        }
    }

    public function getFilters(): array
    {
        return $this->filters;
    }
}
