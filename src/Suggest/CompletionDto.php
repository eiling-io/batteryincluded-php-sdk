<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Suggest;

class CompletionDto
{
    public function __construct(
        private string $value,
        private string $highlighted,
        private float $score,
        private string $source,
    ) {
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function getHighlighted(): string
    {
        return $this->highlighted;
    }

    public function getScore(): float
    {
        return $this->score;
    }

    public function getSource(): string
    {
        return $this->source;
    }
}
