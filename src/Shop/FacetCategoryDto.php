<?php

declare(strict_types=1);

namespace BatteryIncludedSdk\Shop;

class FacetCategoryDto extends FacetDto
{
    private mixed $categories;

    public function __construct(array $data, protected array $appliedFilterValues = [])
    {
        parent::__construct($data, $appliedFilterValues);

        $this->categories = $this->buildCategoryTree($data['counts'] ?? []);
    }

    private function buildCategoryTree(array $items, $separator = '>')
    {
        $tree = [];
        foreach ($this->appliedFilterValues[$this->fieldName] ?? [] as $filter) {
            $appliedFilter = array_map('trim', explode($separator, $filter));
            $filterPrefix = '';
            foreach ($appliedFilter as $filterPart) {
                foreach ($items as $key => $item) {
                    if ($item['value'] === $filterPrefix . $filterPart) {
                        $items[$key]['checked'] = true;
                        $filterPrefix .= $filterPart . ' ' . $separator . ' ';
                    }
                }
            }
        }

        foreach ($items as $item) {
            $parts = array_map('trim', explode($separator, $item['value']));
            $count = $item['count'];
            $checked = $item['checked'] ?? false;

            $current = &$tree;
            foreach ($parts as $part) {
                /* @phpstan-ignore isset.offset */
                if (!isset($current['childs'][$part])) {
                    $current['childs'][$part] = [];
                }
                $current = &$current['childs'][$part];
            }

            $current['count'] = $count;
            $current['checked'] = $checked;
        }

        /* @phpstan-ignore nullCoalesce.offset */
        return $tree['childs'] ?? [];
    }

    public function getCategories(): array
    {
        return $this->categories;
    }
}
