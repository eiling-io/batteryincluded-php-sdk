<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop\Extension;

abstract class AbstractExtension
{
    public function __construct(protected array $data)
    {
    }

    public function getData(): array
    {
        return $this->data;
    }

    abstract public function getType(): string;
}
