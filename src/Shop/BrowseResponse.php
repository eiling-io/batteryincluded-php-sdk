<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

use BatteryIncludedSdk\Service\Response;
use BatteryIncludedSdk\Shop\Extension\AbstractExtension;

class BrowseResponse extends Response
{
    private array $extensions = [];
    private array $teaserExtensions = [];
    private array $redirectsExtension = [];
    private array $codesExtension = [];
    private array $promotionsExtension = [];

    public function __construct(string $responseRaw, private BrowseSearchStruct $searchStruct)
    {
        parent::__construct($responseRaw);

        foreach ($this->getBody()['extensions'] as $extensionData) {
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

    public function getHits(): array
    {
        return $this->getBody()['hits'];
    }

    /**
     * @return FacetDto[]
     */
    public function getFacets(string $categoryField = '_PRODUCT.categories'): array
    {
        $result = [];
        $facets = $this->getBody()['facet_counts'];
        foreach ($facets as $facet) {
            if ($facet['field_name'] === $categoryField) {
                $facet['type'] = 'category';
            }
            switch ($facet['type']) {
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
