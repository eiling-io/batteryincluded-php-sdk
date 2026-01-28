<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Suggest;

use BatteryIncludedSdk\Service\Response;

class SuggestResponse extends Response
{
    private array $documents = [];

    private array $queryCompletions = [];

    private array $facets = [];

    private int $founds = 0;

    private bool $llm = false;

    public function __construct(string $responseRaw, int $statusCode)
    {
        parent::__construct($responseRaw, $statusCode);
        foreach ($this->getBody() as $value) {
            if ($value['llm'] ?? false) {
                $this->llm = true;
            }
            switch ($value['kind']) {
                case 'document':
                    $this->founds = (int) ($value['found'] ?? 0);
                    foreach ($value['hits'] as $document) {
                        $this->documents[] = $document['highlighted'] ?? '';
                    }
                    break;

                case 'query-completion':
                    foreach ($value['hits'] as $completion) {
                        $this->queryCompletions[] = new CompletionDto(
                            $completion['value'] ?? '',
                            $completion['highlighted'] ?? '',
                            (float) $completion['score'],
                            $completion['source'] ?? ''
                        );
                    }
                    break;
                case str_starts_with($value['kind'], 'facet.'):
                    $this->facets[] = $value;
                    break;
            }
        }
    }

    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * @return CompletionDto[]
     */
    public function getQueryCompletions(): array
    {
        return $this->queryCompletions;
    }

    public function getFacets(): array
    {
        return $this->facets;
    }

    public function getFounds(): int
    {
        return $this->founds;
    }

    public function isLLM(): bool
    {
        return $this->llm;
    }
}
