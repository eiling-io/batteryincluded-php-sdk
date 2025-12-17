<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop\Extension;

abstract class AbstractExtension
{
    abstract public function getType(): string;

    abstract public function getData(): array;
}
