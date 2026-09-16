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

    /** @var array<string, AbstractTranslation> additional locales beyond the one written by the flat setters */
    protected array $translations = [];

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

    /**
     * Stores a translation under its own getLocale() - every translation carries its language key
     * itself, so there's no separate locale argument to keep in sync with it.
     */
    protected function storeTranslation(AbstractTranslation $translation): void
    {
        $this->translations[$translation->getLocale()] = $translation;
    }

    /**
     * Builds the _i18n block: $primary (from this DTO's own flat setters, at activeLocale()) plus every
     * addTranslation() entry, each wrapped in the type-scoped key (_PRODUCT/_BLOG) the API expects per
     * locale. A translation without its own url falls back to $urlFallback (e.g. shopUrl()/blogUrl()),
     * so it doesn't need repeating for every locale that shares the same page.
     */
    protected function buildI18n(AbstractTranslation $primary, ?string $urlFallback): array
    {
        $translations = [$primary->getLocale() => $primary];
        foreach ($this->translations as $locale => $translation) {
            $translations[$locale] = $translation;
        }

        return array_map(
            function (AbstractTranslation $translation) use ($urlFallback) {
                $payload = $translation->toArray();
                $payload['url'] ??= $urlFallback;

                return ['_' . $this->getType() => $this->filterJsonValues($payload)];
            },
            $translations
        );
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
