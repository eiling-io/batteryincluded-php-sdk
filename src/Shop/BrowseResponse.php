<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

use BatteryIncludedSdk\Service\Response;

class BrowseResponse extends Response
{
    public function __construct(string $responseRaw, private BrowseSearchStruct $searchStruct)
    {
        parent::__construct($responseRaw);
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
