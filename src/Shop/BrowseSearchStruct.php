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

    protected string $presetId = '';

    protected array $variables = [];

    protected string $locale = 'DE';

    protected ?string $availability = null;

    public function getPresetId(): string
    {
        return $this->presetId;
    }

    public function setPresetId(string $presetId): static
    {
        $this->presetId = $presetId;

        return $this;
    }

    public function setQuery(string $query): static
    {
        $this->query = $query;

        return $this;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function addFilter(string $key, string $value): static
    {
        $this->filters[$key][] = $value;

        return $this;
    }

    public function addRangeFilter(string $key, string $from, string $till): static
    {
        $this->filters[$key]['from'] = $from;
        $this->filters[$key]['till'] = $till;

        return $this;
    }

    public function addFilters(array $filters): static
    {
        foreach ($filters as $key => $values) {
            foreach ($values as $arrayKey => $value) {
                $this->filters[$key][$arrayKey] = $value;
            }
        }

        return $this;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function setPerPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): static
    {
        $this->page = $page;

        return $this;
    }

    public function getSort(): string
    {
        return $this->sort;
    }

    public function setSort(string $sort): static
    {
        $this->sort = $sort;

        return $this;
    }

    public function addVariable(string $name, string $value): static
    {
        $this->variables[$name] = $value;

        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }

    /**
     * Scope of `_PRODUCT_availability` (e.g. country or sales channel), sent as `v[availability]`.
     * Without it the gateway uses the default of the collection variable.
     */
    public function setAvailability(?string $availability): static
    {
        $this->availability = $availability;

        return $this;
    }
}
