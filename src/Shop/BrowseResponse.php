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
    public function getFacets(): array
    {
        $result = [];
        $facets = $this->getBody()['facet_counts'];
        foreach ($facets as $facet) {
            $result[] = new FacetDto($facet, $this->searchStruct->getFilters());
        }

        return $result;
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
