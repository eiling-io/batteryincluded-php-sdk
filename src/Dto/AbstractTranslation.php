<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

abstract class AbstractTranslation
{
    public function __construct(
        private readonly string $locale,
    ) {
    }

    final public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Raw field => value map for this translation (nulls included); AbstractDto::buildI18n()
     * strips them depending on exportNullValues().
     */
    abstract public function toArray(): array;
}
