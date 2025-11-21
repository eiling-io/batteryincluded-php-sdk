<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

interface DtoInterface extends \JsonSerializable
{
    public function getIdentifier(): string;

    public function getType(): string;
}
