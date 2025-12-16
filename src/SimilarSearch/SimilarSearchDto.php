<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\SimilarSearch;

class SimilarSearchDto
{
    public function __construct(
        private string $query,
        private int $count,
        private int $hits,
        private string $highlighted,
    ) {
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getCount(): int
    {
        return $this->count;
    }

    public function getHits(): int
    {
        return $this->hits;
    }

    public function getHighlighted(): string
    {
        return $this->highlighted;
    }
}
