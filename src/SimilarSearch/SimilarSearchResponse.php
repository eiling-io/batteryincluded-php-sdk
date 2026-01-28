<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\SimilarSearch;

use BatteryIncludedSdk\Service\Response;

class SimilarSearchResponse extends Response
{
    private array $similarSearches;

    public function __construct(string $responseRaw, int $statusCode)
    {
        parent::__construct($responseRaw, $statusCode);
        $this->similarSearches = [];
        foreach (($this->getBody()['searches'] ?? []) as $similarSearch) {
            $this->similarSearches[] = new SimilarSearchDto(
                $similarSearch['q'] ?? '',
                (int) ($similarSearch['count'] ?? 0),
                (int) ($similarSearch['hits'] ?? 0),
                $similarSearch['highlighted'] ?? ''
            );
        }
    }

    /**
     * @return array<SimilarSearchDto>
     */
    public function getSimilarSearches(): array
    {
        return $this->similarSearches;
    }
}
