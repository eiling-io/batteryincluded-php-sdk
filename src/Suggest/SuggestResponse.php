<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Suggest;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Service\Response;

class SuggestResponse extends Response
{
    private array $documents = [];

    private array $queryCompletions = [];

    private array $facets = [];

    private int $founds = 0;

    private bool $llm = false;

    /**
     * @param string|null $locale       requested locale, used by getResolvedDocuments()
     * @param string|null $availability requested availability scope, used by getResolvedDocuments()
     */
    public function __construct(
        string $responseRaw,
        int $statusCode,
        private ?string $locale = null,
        private ?string $availability = null,
    ) {
        parent::__construct($responseRaw, $statusCode);
        foreach ($this->getBody() as $value) {
            if (!is_array($value) || !is_string($value['kind'] ?? null)) {
                continue;
            }

            if ($value['llm'] ?? false) {
                $this->llm = true;
            }

            $kind = $value['kind'];
            if ($kind === 'document') {
                $this->founds = (int) ($value['found'] ?? 0);
                foreach ($value['hits'] ?? [] as $document) {
                    $this->documents[] = $document['highlighted'] ?? '';
                }
            } elseif ($kind === 'query-completion') {
                foreach ($value['hits'] ?? [] as $completion) {
                    $this->queryCompletions[] = new CompletionDto(
                        $completion['value'] ?? '',
                        $completion['highlighted'] ?? '',
                        (float) ($completion['score'] ?? 0),
                        $completion['source'] ?? ''
                    );
                }
            } elseif (str_starts_with($kind, 'facet.')) {
                $this->facets[] = $value;
            }
        }
    }

    /**
     * Documents as delivered by the api, translated fields stay in `_PRODUCT_i18n`.
     */
    public function getDocuments(): array
    {
        return $this->documents;
    }

    /**
     * Documents with the translations and then the availability merged into `_PRODUCT`, like BrowseResponse::getHits().
     */
    public function getResolvedDocuments(): array
    {
        return AvailabilityResolver::resolveHits(
            I18nResolver::resolveHits(array_values(array_filter($this->documents, 'is_array')), (string) $this->locale),
            (string) $this->availability
        );
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

    public function getLocale(): ?string
    {
        return $this->locale;
    }

    public function getAvailability(): ?string
    {
        return $this->availability;
    }
}
