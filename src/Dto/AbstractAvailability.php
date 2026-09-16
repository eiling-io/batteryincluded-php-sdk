<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

abstract class AbstractAvailability
{
    public function __construct(
        private readonly string $market,
    ) {
    }

    final public function getMarket(): string
    {
        return $this->market;
    }

    /**
     * Raw field => value map for this market (nulls included); ProductBaseDto::jsonSerialize()
     * strips them depending on exportNullValues().
     */
    abstract public function toArray(): array;
}
