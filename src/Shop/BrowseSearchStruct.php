<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

class BrowseSearchStruct
{
    protected array $filters = [];

    protected string $query = '';

    protected int $perPage = 10;

    protected int $page = 1;

    protected string $sort = '';

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

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function setSort(string $sort): void
    {
        $this->sort = $sort;
    }
}
