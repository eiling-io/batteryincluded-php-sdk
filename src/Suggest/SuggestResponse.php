<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Suggest;

use BatteryIncludedSdk\Service\Response;

class SuggestResponse extends Response
{
    private array $documents = [];

    private array $queryCompletions = [];

    private int $founds = 0;

    public function __construct(string $responseRaw)
    {
        parent::__construct($responseRaw);
        foreach ($this->getBody() as $value) {
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

    public function getFounds(): int
    {
        return $this->founds;
    }
}
