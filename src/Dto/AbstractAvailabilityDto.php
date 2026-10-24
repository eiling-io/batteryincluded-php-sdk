<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

use BatteryIncludedSdk\Availability\AvailabilityResolver;

/**
 * Status, price and stock per scope (e.g. country or sales channel), they overwrite the values of the payload.
 */
abstract class AbstractAvailabilityDto extends AbstractTranslatableDto
{
    protected array $availability = [];

    /**
     * Fields which may differ per scope, null allows every field.
     *
     * @return string[]|null
     */
    protected function getAvailabilityFields(): ?array
    {
        return null;
    }

    public function addAvailability(string $scope, string $field, mixed $value): static
    {
        $availabilityFields = $this->getAvailabilityFields();
        if ($availabilityFields !== null && !in_array($field, $availabilityFields, true)) {
            throw new \InvalidArgumentException(sprintf('Field "%s" is not scoped by availability, allowed fields are: %s', $field, implode(', ', $availabilityFields)));
        }

        $this->availability[AvailabilityResolver::normalizeScope($scope)][$field] = $value;

        return $this;
    }

    public function getAvailabilities(): ?array
    {
        if (count($this->availability) === 0) {
            return null;
        }

        return $this->availability;
    }

    public function getAvailability(string $scope): ?array
    {
        return $this->availability[AvailabilityResolver::normalizeScope($scope)] ?? null;
    }

    /**
     * Availabilities are exported in the root node `_<TYPE>_availability`, scoped like the translations:
     *   _PRODUCT_availability: { de: { active: true, price: 1337.0 }, ch: { active: false } }
     */
    public function jsonSerialize(): array
    {
        $json = parent::jsonSerialize();

        $availabilities = $this->getAvailabilities();
        if ($availabilities === null) {
            return $json;
        }

        // the availabilities precede the payload, like the translations
        $payloadKey = '_' . $this->getType();
        $payload = [$payloadKey => $json[$payloadKey]];
        unset($json[$payloadKey]);

        return $json + [AvailabilityResolver::getKey($this->getType()) => $availabilities] + $payload;
    }
}
