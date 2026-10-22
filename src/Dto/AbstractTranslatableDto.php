<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

use BatteryIncludedSdk\I18n\I18nResolver;

abstract class AbstractTranslatableDto extends AbstractDto
{
    protected array $i18n = [];

    /**
     * Fields which may be translated, null allows every field.
     *
     * @return string[]|null
     */
    protected function getTranslatableFields(): ?array
    {
        return null;
    }

    public function addTranslation(string $locale, string $field, mixed $value): static
    {
        $translatableFields = $this->getTranslatableFields();
        if ($translatableFields !== null && !in_array($field, $translatableFields, true)) {
            throw new \InvalidArgumentException(sprintf('Field "%s" is not translatable, allowed fields are: %s', $field, implode(', ', $translatableFields)));
        }

        $this->i18n[I18nResolver::normalizeLocale($locale)][$field] = $value;

        return $this;
    }

    public function getTranslations(): ?array
    {
        if (count($this->i18n) === 0) {
            return null;
        }

        return $this->i18n;
    }

    public function getTranslation(string $locale): ?array
    {
        return $this->i18n[I18nResolver::normalizeLocale($locale)] ?? null;
    }

    /**
     * Translations are exported in the root node `_<TYPE>_i18n`, scoped by locale:
     *   _PRODUCT_i18n: { de: { name: 'Hersteller' }, en: { name: 'Manufacturer' } }
     */
    public function jsonSerialize(): array
    {
        $json = parent::jsonSerialize();

        $translations = $this->getTranslations();
        if ($translations === null) {
            return $json;
        }

        // the translations precede the payload, like in the schema of BatteryIncluded
        $payloadKey = '_' . $this->getType();
        $payload = [$payloadKey => $json[$payloadKey]];
        unset($json[$payloadKey]);

        return $json + [I18nResolver::getKey($this->getType()) => $translations] + $payload;
    }
}
