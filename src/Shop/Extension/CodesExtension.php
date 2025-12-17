<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop\Extension;

class CodesExtension extends AbstractExtension
{
    public function __construct(private array $data)
    {
    }

    public function getType(): string
    {
        return 'codes';
    }

    public function getData(): array
    {
        return $this->data;
    }
}
