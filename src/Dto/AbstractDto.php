<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Dto;

abstract class AbstractDto implements DtoInterface
{
    protected string $identifier;

    protected string $type;

    public function __construct(string $identifier, string $type)
    {
        $this->identifier = $identifier;
        $this->type = $type;
    }

    final public function getIdentifier(): string
    {
        return $this->getType() . '-' . $this->identifier;
    }

    final public function getType(): string
    {
        return mb_strtoupper($this->type);
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
