<?php

declare(strict_types=1);

use BatteryIncludedSdk\Shop\BrowseResponse;
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;
use BatteryIncludedSdk\Shop\FacetCategoryDto;
use BatteryIncludedSdk\Shop\FacetRangeDto;
use BatteryIncludedSdk\Shop\FacetRatingDto;
use BatteryIncludedSdk\Shop\FacetSelectDto;

require_once __DIR__ . '/app/bootstrap.php';

$lang = demoResolveLanguage();
$query = trim(is_string($_GET['q'] ?? null) ? $_GET['q'] : '');
$sort = is_string($_GET['sort'] ?? null) && isset(DEMO_SORTINGS[$_GET['sort']]) ? $_GET['sort'] : '';
$perPage = in_array((int) ($_GET['per_page'] ?? 0), DEMO_PER_PAGE, true) ? (int) $_GET['per_page'] : DEMO_PER_PAGE[0];
$page = max(1, (int) ($_GET['page'] ?? 1));
$filters = demoRequestFilters();

// state of the page, used by url() to build links
$state = ['q' => $query, 'f' => $filters, 'sort' => $sort, 'per_page' => $perPage === DEMO_PER_PAGE[0] ? null : $perPage, 'page' => $page, 'lang' => $lang];

$searchStruct = new BrowseSearchStruct();
$searchStruct->setQuery($query);
$searchStruct->addFilters($filters);
$searchStruct->setSort($sort);
$searchStruct->setPerPage($perPage);
$searchStruct->setPage($page);
$searchStruct->setLocale(DEMO_LANGUAGES[$lang]['locale']);

$result = null;
$error = null;
try {
    $result = (new BrowseService(demoApiClient()))->browse($searchStruct);
} catch (Throwable $exception) {
    $error = $exception->getMessage();
}

$hits = $result?->getHits() ?? [];
// translated categories of the language, the untranslated categories as long as the api delivers none
$facetCounts = $result?->getBody()['facet_counts'] ?? [];
$categoryField = demoCategoryField($facetCounts);
$facets = $result instanceof BrowseResponse ? demoVisibleFacets($result->getFacets($categoryField), $lang, $categoryField) : [];
$found = $result?->getFound() ?? 0;
$searchTime = (int) ($result?->getBody()['search_time_ms'] ?? 0);

// teasers are placed between the products, all other extensions are shown above the result
$items = array_map(static fn (array $hit) => match ($hit['document']['type'] ?? 'PRODUCT') {
    'BLOG' => ['type' => 'blog', 'id' => (string) ($hit['document']['id'] ?? ''), 'data' => $hit['document']['_BLOG'] ?? []],
    default => ['type' => 'product', 'id' => (string) ($hit['document']['id'] ?? ''), 'data' => $hit['document']['_PRODUCT'] ?? []],
}, $hits);
foreach ($result?->getTeaserExtensions() ?? [] as $teaser) {
    $position = max(0, min(count($items), (int) ($teaser->getData()['position'] ?? 1) - 1));
    array_splice($items, $position, 0, [['type' => 'teaser', 'data' => $teaser->getData()]]);
}

$facetLabels = array_map(static fn (array $facet) => $facet['label'], $facets);

$pageTitle = $query;
$homeUrl = url(['q' => null, 'f' => null, 'sort' => null, 'page' => null]);
// filters are language specific, they are kept only for the current language
$languageUrl = static fn (string $code) => url(['lang' => $code, 'f' => $code === $lang ? $filters : null, 'page' => null]);
require __DIR__ . '/app/partials/header.php';
?>

<main class="container-xxl py-4">
    <?php if ($error !== null) { ?>
        <div class="alert alert-danger d-flex gap-2" role="alert">
            <i class="bi bi-exclamation-octagon" aria-hidden="true"></i>
            <div><strong><?php echo e(t('error')); ?></strong><br><code><?php echo e($error); ?></code></div>
        </div>
    <?php } ?>

    <div class="row g-4">
        <aside class="col-lg-3">
            <div class="offcanvas-lg offcanvas-start" tabindex="-1" id="filters" aria-labelledby="filters-title">
                <div class="offcanvas-header border-bottom">
                    <h2 class="offcanvas-title h5" id="filters-title"><?php echo e(t('filters')); ?></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#filters" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body d-block">
                    <form id="filter-form" action="index.php" method="get" data-auto-submit>
                        <input type="hidden" name="q" value="<?php echo e($query); ?>">
                        <input type="hidden" name="lang" value="<?php echo e($lang); ?>">
                        <?php foreach ($filters as $field => $values) {
                            // filters without visible facet (e.g. categories) are kept as hidden fields
                            if (isset($facets[$field]) && !$facets[$field]['facet'] instanceof FacetCategoryDto) {
                                continue;
                            }
                            foreach ($values as $key => $value) { ?>
                                <input type="hidden" name="<?php echo e('f[' . $field . '][' . (is_int($key) ? '' : $key) . ']'); ?>" value="<?php echo e($value); ?>">
                            <?php }
                            } ?>

                        <?php foreach ($facets as $field => ['facet' => $facet, 'label' => $label]) {
                            $id = 'facet-' . md5($field); ?>
                            <details class="facet" open>
                                <summary class="facet-title"><?php echo e($label); ?></summary>
                                <div class="facet-body">
                                    <?php if ($facet instanceof FacetCategoryDto) { ?>
                                        <?php if (isset($filters[$field])) { ?>
                                            <a class="category-link category-reset" href="<?php echo e(urlWithoutFilter($field)); ?>">
                                                <span><i class="bi bi-chevron-left" aria-hidden="true"></i> <?php echo e(t('all_categories')); ?></span>
                                            </a>
                                        <?php } ?>
                                        <?php echo categoryTree($facet->getCategories(), $field); ?>

                                    <?php } elseif ($facet instanceof FacetRangeDto) {
                                        $applied = $filters[$field] ?? null; ?>
                                        <div class="d-flex align-items-center gap-2">
                                            <input class="form-control form-control-sm" type="number" step="any" inputmode="decimal"
                                                   name="<?php echo e('f[' . $field . '][from]'); ?>" aria-label="<?php echo e($label . ' ' . t('from')); ?>"
                                                   min="<?php echo e(floor($facet->getMin())); ?>" max="<?php echo e(ceil($facet->getMax())); ?>"
                                                   placeholder="<?php echo e(floor($facet->getMin())); ?>" value="<?php echo e($applied !== null ? $facet->getSelectedMin() : ''); ?>">
                                            <span class="text-body-secondary">–</span>
                                            <input class="form-control form-control-sm" type="number" step="any" inputmode="decimal"
                                                   name="<?php echo e('f[' . $field . '][till]'); ?>" aria-label="<?php echo e($label . ' ' . t('till')); ?>"
                                                   min="<?php echo e(floor($facet->getMin())); ?>" max="<?php echo e(ceil($facet->getMax())); ?>"
                                                   placeholder="<?php echo e(ceil($facet->getMax())); ?>" value="<?php echo e($applied !== null ? $facet->getSelectedMax() : ''); ?>">
                                            <?php if ($facet->getFieldUnit() !== '') { ?>
                                                <span class="text-body-secondary"><?php echo e($facet->getFieldUnit()); ?></span>
                                            <?php } ?>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary w-100 mt-2" type="submit"><?php echo e(t('apply')); ?></button>

                                    <?php } elseif ($facet instanceof FacetRatingDto || $facet instanceof FacetSelectDto) {
                                        $values = $facet instanceof FacetRatingDto ? $facet->getRatings() : $facet->getValues();
                                        if ($facet instanceof FacetRatingDto) {
                                            usort($values, static fn ($a, $b) => (float) $b->getName() <=> (float) $a->getName());
                                        }
                                        $values = array_values($values);
                                        $limit = 6; ?>
                                        <?php foreach ($values as $index => $value) {
                                            $valueId = $id . '-' . $index;
                                            if ($index === $limit) { ?>
                                                <div class="collapse" id="<?php echo e($id); ?>-more">
                                            <?php } ?>
                                            <div class="form-check facet-option">
                                                <input class="form-check-input" type="checkbox" id="<?php echo e($valueId); ?>"
                                                       name="<?php echo e('f[' . $field . '][]'); ?>" value="<?php echo e($value->getName()); ?>"<?php echo $value->isChecked() ? ' checked' : ''; ?>>
                                                <label class="form-check-label" for="<?php echo e($valueId); ?>">
                                                    <?php if ($facet instanceof FacetRatingDto) { ?>
                                                        <?php echo stars((float) $value->getName()); ?>
                                                    <?php } else { ?>
                                                        <?php echo e(trim(facetValueLabel($field, $value->getName()) . ' ' . $facet->getFieldUnit())); ?>
                                                    <?php } ?>
                                                </label>
                                                <span class="facet-count"><?php echo e($value->getCount()); ?></span>
                                            </div>
                                        <?php } ?>
                                        <?php if (count($values) > $limit) { ?>
                                            </div>
                                            <button class="btn btn-link btn-sm px-0 facet-more collapsed" type="button" data-bs-toggle="collapse"
                                                    data-bs-target="#<?php echo e($id); ?>-more" aria-expanded="false" aria-controls="<?php echo e($id); ?>-more">
                                                <span class="when-collapsed"><?php echo e(t('show_more')); ?> (<?php echo count($values) - $limit; ?>)</span>
                                                <span class="when-expanded"><?php echo e(t('show_less')); ?></span>
                                            </button>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                            </details>
                        <?php } ?>
                    </form>
                </div>
            </div>
        </aside>

        <section class="col-lg-9" aria-labelledby="result-title">
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#filters" aria-controls="filters">
                    <i class="bi bi-funnel" aria-hidden="true"></i> <?php echo e(t('filters')); ?>
                    <?php if ($filters !== []) { ?><span class="badge text-bg-primary"><?php echo count($filters); ?></span><?php } ?>
                </button>
                <h1 class="h5 mb-0 me-auto" id="result-title">
                    <?php echo e($query !== '' ? t('results_for', formatNumber($found), $query) : t('results', formatNumber($found))); ?>
                    <?php if ($result !== null) { ?><small class="text-body-secondary fw-normal fs-6"><?php echo e(t('search_time', $searchTime)); ?></small><?php } ?>
                </h1>
                <div class="d-flex gap-2">
                    <label class="visually-hidden" for="sort"><?php echo e(t('sort')); ?></label>
                    <select class="form-select form-select-sm w-auto" id="sort" name="sort" form="filter-form">
                        <?php foreach (DEMO_SORTINGS as $value => $key) { ?>
                            <option value="<?php echo e($value); ?>"<?php echo $value === $sort ? ' selected' : ''; ?>><?php echo e(t('sort')); ?>: <?php echo e(t($key)); ?></option>
                        <?php } ?>
                    </select>
                    <label class="visually-hidden" for="per-page"><?php echo e(t('per_page')); ?></label>
                    <select class="form-select form-select-sm w-auto" id="per-page" name="per_page" form="filter-form">
                        <?php foreach (DEMO_PER_PAGE as $value) { ?>
                            <option value="<?php echo $value; ?>"<?php echo $value === $perPage ? ' selected' : ''; ?>><?php echo $value; ?> <?php echo e(t('per_page')); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <?php if ($filters !== []) { ?>
                <div class="d-flex flex-wrap gap-2 mb-3" aria-label="<?php echo e(t('filters')); ?>">
                    <?php foreach ($filters as $field => $values) {
                        $label = $facetLabels[$field] ?? $field;
                        if (isset($values['from']) || isset($values['till'])) { ?>
                            <a class="filter-chip" href="<?php echo e(urlWithoutFilter($field)); ?>" title="<?php echo e(t('remove_filter')); ?>">
                                <span class="text-body-secondary"><?php echo e($label); ?>:</span>
                                <?php echo e(formatNumber((float) $values['from'])); ?> – <?php echo e((float) $values['till'] >= PHP_INT_MAX ? '∞' : formatNumber((float) $values['till'])); ?>
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </a>
                        <?php continue;
                        }
                        foreach ($values as $value) { ?>
                            <a class="filter-chip" href="<?php echo e(urlWithoutFilter($field, $value)); ?>" title="<?php echo e(t('remove_filter')); ?>">
                                <span class="text-body-secondary"><?php echo e($label); ?>:</span> <?php echo e(facetValueLabel($field, $value)); ?>
                                <i class="bi bi-x-lg" aria-hidden="true"></i>
                            </a>
                        <?php }
                        } ?>
                    <a class="btn btn-link btn-sm" href="<?php echo e(url(['f' => null, 'page' => null])); ?>"><?php echo e(t('reset_filters')); ?></a>
                </div>
            <?php } ?>

            <?php foreach ($result?->getRedirectsExtensions() ?? [] as $redirect) { ?>
                <div class="alert alert-warning d-flex align-items-center gap-2">
                    <i class="bi bi-signpost-split" aria-hidden="true"></i>
                    <div><?php echo e(t('redirect_notice')); ?> <a class="alert-link" href="<?php echo e($redirect->getData()['url'] ?? '#'); ?>"><?php echo e($redirect->getData()['name'] ?? $redirect->getData()['url'] ?? ''); ?></a></div>
                </div>
            <?php } ?>
            <?php foreach ($result?->getCodesExtensions() ?? [] as $code) { ?>
                <div class="alert alert-info d-flex align-items-center gap-2">
                    <i class="bi bi-ticket-perforated" aria-hidden="true"></i>
                    <div><?php echo e(t('code_notice')); ?> <strong><?php echo e($code->getData()['name'] ?? ''); ?></strong></div>
                </div>
            <?php } ?>
            <?php foreach ($result?->getPromotionsExtensions() ?? [] as $promotion) {
                $data = $promotion->getData(); ?>
                <a class="promotion d-block mb-3" href="<?php echo e($data['url'] ?? '#'); ?>">
                    <picture>
                        <source media="(max-width: 575px)" srcset="<?php echo e($data['image']['mobile'] ?? ''); ?>">
                        <img src="<?php echo e($data['image']['desktop'] ?? ''); ?>" alt="<?php echo e($data['image']['alt'] ?? ''); ?>" loading="lazy">
                    </picture>
                    <span class="promotion-title"><?php echo e($data['name'] ?? ''); ?></span>
                </a>
            <?php } ?>

            <?php if ($items === [] && $error === null) { ?>
                <div class="text-center py-5 text-body-secondary">
                    <i class="bi bi-search display-4" aria-hidden="true"></i>
                    <h2 class="h5 mt-3"><?php echo e(t('no_results')); ?></h2>
                    <p><?php echo e(t('no_results_hint')); ?></p>
                </div>
            <?php } ?>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3 row-cols-xxl-4 g-3">
                <?php foreach ($items as $item) {
                    $data = $item['data']; ?>
                    <div class="col">
                        <?php if ($item['type'] === 'teaser') { ?>
                            <a class="card h-100 teaser text-decoration-none overflow-hidden" href="<?php echo e($data['url'] ?? '#'); ?>">
                                <picture>
                                    <source media="(max-width: 575px)" srcset="<?php echo e($data['image']['mobile'] ?? ''); ?>">
                                    <img class="card-img h-100 object-fit-cover" src="<?php echo e($data['image']['desktop'] ?? ''); ?>" alt="<?php echo e($data['image']['alt'] ?? ''); ?>" loading="lazy">
                                </picture>
                                <div class="card-img-overlay d-flex align-items-end">
                                    <span class="badge text-bg-dark fs-6"><?php echo e($data['name'] ?? ''); ?></span>
                                </div>
                            </a>
                        <?php } elseif ($item['type'] === 'blog') { ?>
                            <article class="card h-100 product">
                                <?php if (!empty($data['previewImage'])) { ?>
                                    <div class="product-image">
                                        <img src="<?php echo e($data['previewImage']); ?>" alt="<?php echo e($data['title'] ?? ''); ?>" loading="lazy">
                                    </div>
                                <?php } ?>
                                <div class="card-body d-flex flex-column">
                                    <div class="small text-uppercase text-body-secondary fw-semibold">
                                        <i class="bi bi-journal-text" aria-hidden="true"></i> <?php echo e(t('blog')); ?>
                                    </div>
                                    <h2 class="h6 card-title mb-1">
                                        <a class="stretched-link text-reset text-decoration-none" href="<?php echo e(detailUrl($item['id'])); ?>"><?php echo e($data['title'] ?? ''); ?></a>
                                    </h2>
                                    <p class="card-text small text-body-secondary line-clamp"><?php echo e($data['shortDescription'] ?? $data['description'] ?? ''); ?></p>
                                    <div class="mt-auto small text-body-secondary">
                                        <?php echo e(implode(' · ', array_filter([$data['author'] ?? null, isset($data['publishedAt']) ? formatDate((string) $data['publishedAt']) : null]))); ?>
                                    </div>
                                </div>
                            </article>
                        <?php } else {
                            $instock = (int) ($data['instock'] ?? 0); ?>
                            <article class="card h-100 product">
                                <div class="product-image">
                                    <img src="<?php echo e($data['imageUrl'] ?? ''); ?>" alt="<?php echo e($data['name'] ?? ''); ?>" loading="lazy">
                                </div>
                                <div class="card-body d-flex flex-column">
                                    <?php if (!empty($data['manufacture'])) { ?>
                                        <div class="small text-uppercase text-body-secondary fw-semibold"><?php echo e($data['manufacture']); ?></div>
                                    <?php } ?>
                                    <h2 class="h6 card-title mb-1">
                                        <a class="stretched-link text-reset text-decoration-none" href="<?php echo e(detailUrl($item['id'])); ?>"><?php echo e($data['name'] ?? ''); ?></a>
                                    </h2>
                                    <?php if (isset($data['rating'])) { ?>
                                        <div class="small mb-2"><?php echo stars((float) $data['rating']); ?> <span class="text-body-secondary"><?php echo e(formatNumber((float) $data['rating'])); ?></span></div>
                                    <?php } ?>
                                    <p class="card-text small text-body-secondary line-clamp"><?php echo e($data['description'] ?? ''); ?></p>
                                    <div class="mt-auto d-flex align-items-end justify-content-between gap-2">
                                        <div>
                                            <div class="fs-5 fw-semibold"><?php echo isset($data['price']) ? e(formatPrice((float) $data['price'])) : ''; ?></div>
                                            <div class="small <?php echo $instock > 0 ? 'text-success' : 'text-danger'; ?>">
                                                <i class="bi <?php echo $instock > 0 ? 'bi-check-circle' : 'bi-x-circle'; ?>" aria-hidden="true"></i>
                                                <?php echo e($instock > 0 ? t('in_stock', $instock) : t('out_of_stock')); ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($data['ordernumber'])) { ?>
                                            <div class="small text-body-secondary text-end"><?php echo e(t('item_number')); ?><br><?php echo e($data['ordernumber']); ?></div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </article>
                        <?php } ?>
                    </div>
                <?php } ?>
            </div>

            <?php if ($result !== null && $result->getPages() > 1) {
                $pagination = pagination($page, $result->getPages()); ?>
                <nav class="mt-4" aria-label="<?php echo e(t('pagination')); ?>">
                    <ul class="pagination justify-content-center flex-wrap">
                        <li class="page-item<?php echo $page <= 1 ? ' disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo e(url(['page' => $page - 1])); ?>" aria-label="<?php echo e(t('page_prev')); ?>"><i class="bi bi-chevron-left" aria-hidden="true"></i></a>
                        </li>
                        <?php foreach ($pagination['pages'] as $number) { ?>
                            <?php if ($number === null) { ?>
                                <li class="page-item disabled"><span class="page-link">…</span></li>
                            <?php } else { ?>
                                <li class="page-item<?php echo $number === $page ? ' active' : ''; ?>">
                                    <a class="page-link" href="<?php echo e(url(['page' => $number])); ?>"<?php echo $number === $page ? ' aria-current="page"' : ''; ?>><?php echo $number; ?></a>
                                </li>
                            <?php } ?>
                        <?php } ?>
                        <li class="page-item<?php echo $page >= $pagination['last'] ? ' disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo e(url(['page' => $page + 1])); ?>" aria-label="<?php echo e(t('page_next')); ?>"><i class="bi bi-chevron-right" aria-hidden="true"></i></a>
                        </li>
                    </ul>
                </nav>
            <?php } ?>

            <?php if ($result !== null) { ?>
                <details class="api-details mt-5">
                    <summary class="text-body-secondary"><i class="bi bi-braces" aria-hidden="true"></i> <?php echo e(t('api_details')); ?></summary>
                    <div class="row g-3 mt-1">
                        <div class="col-12">
                            <h3 class="h6"><?php echo e(t('api_request')); ?></h3>
                            <pre><code><?php echo e(json_encode([
                                'query' => $searchStruct->getQuery(),
                                'filters' => $searchStruct->getFilters(),
                                'sort' => $searchStruct->getSort(),
                                'page' => $searchStruct->getPage(),
                                'perPage' => $searchStruct->getPerPage(),
                                'locale' => $searchStruct->getLocale(),
                            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></code></pre>
                        </div>
                        <?php if ($hits !== []) { ?>
                            <div class="col-md-6">
                                <h3 class="h6"><?php echo e(t('raw_hit')); ?></h3>
                                <pre><code><?php echo e(json_encode($result->getRawHits()[0]['document'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></code></pre>
                            </div>
                            <div class="col-md-6">
                                <h3 class="h6"><?php echo e(t('resolved_hit', $result->getLocale())); ?></h3>
                                <pre><code><?php echo e(json_encode($hits[0]['document'] ?? null, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)); ?></code></pre>
                            </div>
                        <?php } ?>
                    </div>
                </details>
            <?php } ?>
        </section>
    </div>
</main>

<?php require __DIR__ . '/app/partials/footer.php'; ?>
