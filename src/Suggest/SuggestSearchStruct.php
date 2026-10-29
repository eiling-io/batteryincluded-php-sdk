<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Suggest;

class SuggestSearchStruct
{
    protected array $filters = [];

    protected string $query = '';

    protected string $locale = 'DE';

    protected ?string $availability = null;

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
