<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

use BatteryIncludedSdk\Availability\AvailabilityResolver;
use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Shop\Extension\AbstractExtension;

class BrowseResponse extends Response
{
    private array $extensions = [];
    private array $teaserExtensions = [];
    private array $redirectsExtension = [];
    private array $codesExtension = [];
    private array $promotionsExtension = [];

    public function __construct(string $responseRaw, private BrowseSearchStruct $searchStruct, int $statusCode = 200)
    {
        parent::__construct($responseRaw, $statusCode);

        foreach ($this->getBody()['extensions'] ?? [] as $extensionData) {
            switch ($extensionData['type']) {
                case 'teaser':
                    $this->teaserExtensions[] = $this->extensions[] = new Extension\TeaserExtension($extensionData['data']);
                    break;
                case 'redirects':
                    $this->redirectsExtension[] = $this->extensions[] = new Extension\RedirectsExtension($extensionData['data']);
                    break;
                case 'codes':
                    $this->codesExtension[] = $this->extensions[] = new Extension\CodesExtension($extensionData['data']);
                    break;
                case 'promotions':
                    $this->promotionsExtension[] = $this->extensions[] = new Extension\PromotionsExtension($extensionData['data']);
                    break;
            }
        }
    }

    /**
     * Hits with the translations of the requested locale and then the availability merged into the documents.
     */
    public function getHits(): array
    {
        return AvailabilityResolver::resolveHits(
            I18nResolver::resolveHits($this->getRawHits(), $this->getLocale()),
            (string) $this->getAvailability()
        );
    }

    /**
     * Hits as delivered by the api, including the untouched _i18n and _availability nodes.
     */
    public function getRawHits(): array
    {
        return $this->getBody()['hits'] ?? [];
    }

    public function getLocale(): string
    {
        return (string) ($this->searchStruct->getVariables()['locale'] ?? $this->searchStruct->getLocale());
    }

    /**
     * Requested availability scope, null if the gateway used the default of the collection variable.
     */
    public function getAvailability(): ?string
    {
        $availability = $this->searchStruct->getVariables()[AvailabilityResolver::VARIABLE] ?? $this->searchStruct->getAvailability();

        return $availability !== null ? (string) $availability : null;
    }

    /**
     * @return FacetDto[]
     */
    public function getFacets(string $categoryField = '_PRODUCT.categories'): array
    {
        $result = [];
        $facets = $this->getBody()['facet_counts'] ?? [];
        foreach ($facets as $facet) {
            if (($facet['field_name'] ?? null) === $categoryField) {
                $facet['type'] = 'category';
            }
            // The api omits the type for facets without counts, there is nothing to render then.
            switch ($facet['type'] ?? null) {
                case 'category':
                    $result[] = new FacetCategoryDto($facet, $this->searchStruct->getFilters());
                    break;
                case 'range':
                    $result[] = new FacetRangeDto($facet, $this->searchStruct->getFilters());
                    break;
                case 'select':
                    $result[] = new FacetSelectDto($facet, $this->searchStruct->getFilters());
                    break;
                case 'rating':
                    $result[] = new FacetRatingDto($facet, $this->searchStruct->getFilters());
                    break;
            }
        }

        return $result;
    }

    /**
     * @return AbstractExtension[]
     */
    public function getAllExtensions(): array
    {
        return $this->extensions;
    }

    public function getTeaserExtensions(): array
    {
        return $this->teaserExtensions;
    }

    public function getRedirectsExtensions(): array
    {
        return $this->redirectsExtension;
    }

    public function getCodesExtensions(): array
    {
        return $this->codesExtension;
    }

    public function getPromotionsExtensions(): array
    {
        return $this->promotionsExtension;
    }

    public function getFound(): int
    {
        return (int) $this->getBody()['found'];
    }

    public function getPages(): int
    {
        return (int) ceil($this->getBody()['found'] / $this->searchStruct->getPerPage());
    }

    public function getPage(): int
    {
        return $this->searchStruct->getPage();
    }
}
