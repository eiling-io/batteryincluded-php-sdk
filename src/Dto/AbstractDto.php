<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

abstract class AbstractDto implements DtoInterface
{
    /** used only if the DTO is serialized without ever going through a SyncService (e.g. in isolation) */
    protected const FALLBACK_LOCALE = 'de';

    protected string $identifier;

    protected string $type;

    protected bool $exportNullValues = false;

    protected ?string $locale = null;

    public function __construct(string $identifier, string $type)
    {
        $this->identifier = $identifier;
        $this->type = $type;
    }

    /**
     * Pins the locale that the flat setters (setName(), setDescription(), ...) write into.
     * Leave unset to use the SDK's configured default locale (ApiClient::getDefaultLocale()).
     */
    public function locale(string $locale): static
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Called by SyncService right before serialization; does nothing if locale() was already set explicitly.
     */
    public function resolveLocale(string $fallback): void
    {
        $this->locale ??= $fallback;
    }

    protected function activeLocale(): string
    {
        return $this->locale ?? static::FALLBACK_LOCALE;
    }

    final public function getIdentifier(): string
    {
        return $this->getType() . '-' . $this->identifier;
    }

    final public function getType(): string
    {
        return mb_strtoupper($this->type);
    }

    public function exportNullValues(bool $export = true): static
    {
        $this->exportNullValues = $export;

        return $this;
    }

    protected function filterJsonValues(array $values): array
    {
        if ($this->exportNullValues) {
            return $values;
        }

        return array_filter($values, static fn ($value) => $value !== null);
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getIdentifier(),
            'type' => $this->type,
            '_' . $this->getType() => [],
        ];
    }
}
