<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop\Extension;

class TeaserExtension extends AbstractExtension
{
    public function __construct(private array $data)
    {
    }

    public function getType(): string
    {
        return 'teaser';
    }

    public function getData(): array
    {
        return $this->data;
    }
}
