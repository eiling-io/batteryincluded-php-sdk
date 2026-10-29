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
            if (!is_array($extensionData) || !is_array($extensionData['data'] ?? null)) {
                continue;
            }

            switch ($extensionData['type'] ?? null) {
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
     * Facets of the filters configured in the backend. The gateway also delivers the facets of all other locales
     * (`_PRODUCT_i18n.de.properties.Color` next to `_PRODUCT_i18n.properties.Color`), they are skipped.
     *
     * @param string|null $categoryField field of the category tree, by default `_<TYPE>_i18n.categories` if the
     *                                   response contains it, otherwise `_<TYPE>.categories`
     *
     * @return FacetDto[]
     */
    public function getFacets(?string $categoryField = null): array
    {
        $result = [];
        $facets = array_filter(
            $this->getBody()['facet_counts'] ?? [],
            fn ($facet) => is_array($facet) && !$this->isFacetOfOtherLocale((string) ($facet['field_name'] ?? ''))
        );
        $categoryFields = $categoryField !== null ? [$categoryField] : $this->findCategoryFields($facets);
        foreach ($facets as $facet) {
            if (in_array($facet['field_name'] ?? null, $categoryFields, true)) {
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
     * Facets of the requested locale have no locale in their name, the other ones do: `_PRODUCT_i18n.de.<field>`.
     */
    private function isFacetOfOtherLocale(string $field): bool
    {
        if (preg_match('/^_[^.]+' . I18nResolver::SUFFIX . '\.([a-z]{2}(?:-[a-z0-9]{2,8})*)\./', $field, $matches) !== 1) {
            return false;
        }

        return $matches[1] !== I18nResolver::normalizeLocale($this->getLocale());
    }

    /**
     * One category tree per type, the translated one wins.
     *
     * @return string[]
     */
    private function findCategoryFields(array $facets): array
    {
        $categoryFields = [];
        foreach ($facets as $facet) {
            $field = (string) ($facet['field_name'] ?? '');
            if (preg_match('/^(_[^.]+?)(' . I18nResolver::SUFFIX . ')?\.categories$/', $field, $matches) !== 1) {
                continue;
            }

            $isTranslated = isset($matches[2]);
            if ($isTranslated || !isset($categoryFields[$matches[1]])) {
                $categoryFields[$matches[1]] = $field;
            }
        }

        return array_values($categoryFields);
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
        return (int) ($this->getBody()['found'] ?? 0);
    }

    public function getPages(): int
    {
        return (int) ceil($this->getFound() / max(1, $this->searchStruct->getPerPage()));
    }

    public function getPage(): int
    {
        return $this->searchStruct->getPage();
    }
}
