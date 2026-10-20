<?php

declare(strict_types=1);

use BatteryIncludedSdk\I18n\I18nResolver;
use BatteryIncludedSdk\Shop\BrowseSearchStruct;
use BatteryIncludedSdk\Shop\BrowseService;

require_once __DIR__ . '/app/bootstrap.php';

$lang = demoResolveLanguage();
$id = trim(is_string($_GET['id'] ?? null) ? $_GET['id'] : '');

// the browse endpoint delivers a single document with a filter on its id
$searchStruct = new BrowseSearchStruct();
$searchStruct->addFilter('id', $id);
$searchStruct->setPerPage(1);
$searchStruct->setLocale(DEMO_LANGUAGES[$lang]['locale']);

$result = null;
$error = null;
if ($id !== '') {
    try {
        $result = (new BrowseService(demoApiClient()))->browse($searchStruct);
    } catch (Throwable $exception) {
        $error = $exception->getMessage();
    }
}

$rawHit = $result?->getRawHits()[0] ?? null;
$document = $result?->getHits()[0]['document'] ?? null;
$type = (string) ($document['type'] ?? '');
$payloadKey = '_' . $type;
$data = $document[$payloadKey] ?? [];

// fields delivered by the gateway in `_<TYPE>_i18n` for the requested locale, all others are the default values
$translations = $rawHit['document'][I18nResolver::getKey($type)] ?? [];
$translatedFields = is_array($translations) ? array_keys(I18nResolver::resolveDocument([$payloadKey => [], I18nResolver::getKey($type) => $translations], $lang)[$payloadKey]) : [];

// categories link to the search, filtered with the translated categories if the document has some
$categoryField = in_array('categories', $translatedFields, true) ? I18nResolver::getKey('PRODUCT') . '.categories' : '_PRODUCT.categories';
$properties = array_merge(...array_values(array_filter((array) ($data['properties'] ?? []), 'is_array')) ?: [[]]);

// labels of the properties are translated in the backend and delivered with the facets
$facetLabels = [];
foreach ($result?->getFacets($categoryField) ?? [] as $facet) {
    $facetLabels[$facet->getFieldName()] = $facet->getFieldLabel();
}

// back to the search the visitor came from, otherwise to the start page
$referer = (string) ($_SERVER['HTTP_REFERER'] ?? '');
$backUrl = str_contains($referer, '/index.php') && parse_url($referer, PHP_URL_HOST) === ($_SERVER['HTTP_HOST'] ?? null)
    ? $referer
    : 'index.php?' . http_build_query(['lang' => $lang]);

$state = ['lang' => $lang];
$query = '';
$pageTitle = (string) ($data['name'] ?? $data['title'] ?? $id);
$homeUrl = 'index.php?' . http_build_query(['lang' => $lang]);
$languageUrl = static fn (string $code) => detailUrl($id, $code);
$json = static fn (mixed $value) => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
require __DIR__ . '/app/partials/header.php';
?>

<main class="container-xxl py-4">
    <a class="btn btn-link px-0 mb-3" href="<?php echo e($backUrl); ?>">
        <i class="bi bi-chevron-left" aria-hidden="true"></i> <?php echo e(t('back_to_search')); ?>
    </a>

    <?php if ($error !== null) { ?>
        <div class="alert alert-danger d-flex gap-2" role="alert">
            <i class="bi bi-exclamation-octagon" aria-hidden="true"></i>
            <div><strong><?php echo e(t('error')); ?></strong><br><code><?php echo e($error); ?></code></div>
        </div>
    <?php } elseif ($document === null) { ?>
        <div class="text-center py-5 text-body-secondary">
            <i class="bi bi-question-circle display-4" aria-hidden="true"></i>
            <h1 class="h5 mt-3"><?php echo e(t('not_found')); ?></h1>
            <p><?php echo e(t('not_found_hint', $id, mb_strtoupper($lang))); ?></p>
        </div>
    <?php } else { ?>
        <article class="detail card mb-4">
            <div class="row g-0">
                <?php $image = (string) ($data['imageUrl'] ?? $data['previewImage'] ?? ''); ?>
                <?php if ($image !== '') { ?>
                    <div class="col-md-5 p-4 pe-md-0 d-flex align-items-center">
                        <div class="detail-image">
                            <img src="<?php echo e($image); ?>" alt="<?php echo e($pageTitle); ?>">
                        </div>
                    </div>
                <?php } ?>
                <div class="col">
                    <div class="card-body p-4">
                        <?php if ($type === 'BLOG') { ?>
                            <div class="small text-uppercase text-body-secondary fw-semibold">
                                <i class="bi bi-journal-text" aria-hidden="true"></i> <?php echo e(t('blog')); ?>
                            </div>
                            <h1 class="h3 mt-1"><?php echo e($data['title'] ?? ''); ?></h1>
                            <div class="text-body-secondary mb-3">
                                <?php echo e(implode(' · ', array_filter([$data['author'] ?? null, isset($data['publishedAt']) ? formatDate((string) $data['publishedAt']) : null]))); ?>
                            </div>
                            <?php if (!empty($data['shortDescription'])) { ?>
                                <p class="lead"><?php echo e($data['shortDescription']); ?></p>
                            <?php } ?>
                            <p><?php echo e($data['description'] ?? ''); ?></p>
                            <?php if (!empty($data['blogUrl'])) { ?>
                                <a class="btn btn-primary" href="<?php echo e($data['blogUrl']); ?>" target="_blank" rel="noopener">
                                    <?php echo e(t('read_article')); ?> <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                </a>
                            <?php } ?>
                        <?php } else {
                            $instock = (int) ($data['instock'] ?? 0); ?>
                            <?php if (!empty($data['manufacture'])) { ?>
                                <div class="small text-uppercase text-body-secondary fw-semibold"><?php echo e($data['manufacture']); ?></div>
                            <?php } ?>
                            <h1 class="h3 mt-1"><?php echo e($data['name'] ?? ''); ?></h1>
                            <?php if (isset($data['rating'])) { ?>
                                <div class="mb-3"><?php echo stars((float) $data['rating']); ?> <span class="text-body-secondary"><?php echo e(formatNumber((float) $data['rating'])); ?></span></div>
                            <?php } ?>
                            <div class="d-flex flex-wrap align-items-end gap-4 mb-3">
                                <div>
                                    <div class="fs-3 fw-semibold"><?php echo isset($data['price']) ? e(formatPrice((float) $data['price'])) : ''; ?></div>
                                    <div class="<?php echo $instock > 0 ? 'text-success' : 'text-danger'; ?>">
                                        <i class="bi <?php echo $instock > 0 ? 'bi-check-circle' : 'bi-x-circle'; ?>" aria-hidden="true"></i>
                                        <?php echo e($instock > 0 ? t('in_stock', $instock) : t('out_of_stock')); ?>
                                    </div>
                                </div>
                                <?php if (!empty($data['shopUrl'])) { ?>
                                    <a class="btn btn-primary" href="<?php echo e($data['shopUrl']); ?>" target="_blank" rel="noopener">
                                        <?php echo e(t('to_product')); ?> <i class="bi bi-box-arrow-up-right" aria-hidden="true"></i>
                                    </a>
                                <?php } ?>
                            </div>
                            <p><?php echo e($data['description'] ?? ''); ?></p>
                            <dl class="row small mb-3">
                                <?php foreach (['ordernumber' => 'item_number', 'manufactureNumber' => 'manufacture_number', 'ean' => 'ean'] as $field => $label) {
                                    if (!empty($data[$field])) { ?>
                                        <dt class="col-sm-4 text-body-secondary fw-normal"><?php echo e(t($label)); ?></dt>
                                        <dd class="col-sm-8 mb-1"><?php echo e($data[$field]); ?></dd>
                                    <?php }
                                    } ?>
                            </dl>
                            <?php if (!empty($data['categories'])) { ?>
                                <div class="d-flex flex-wrap gap-2 mb-3">
                                    <?php foreach ((array) $data['categories'] as $category) { ?>
                                        <a class="badge rounded-pill text-bg-light border text-decoration-none fw-normal"
                                           href="<?php echo e(url(['f' => [$categoryField => [$category]]])); ?>"><?php echo e($category); ?></a>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                            <?php if ($properties !== []) { ?>
                                <h2 class="h6 mt-4"><?php echo e(t('properties')); ?></h2>
                                <table class="table table-sm small mb-0">
                                    <tbody>
                                    <?php foreach ($properties as $name => $values) { ?>
                                        <tr>
                                            <th class="text-body-secondary fw-normal w-50" scope="row"><?php echo e($facetLabels[I18nResolver::getKey('PRODUCT') . '.properties.' . $name] ?? $facetLabels['_PRODUCT.properties.' . $name] ?? $name); ?></th>
                                            <td><?php echo e(implode(', ', (array) $values)); ?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </article>

        <section class="card mb-4">
            <div class="card-body">
                <h2 class="h5"><?php echo e(t('all_data')); ?></h2>
                <p class="small text-body-secondary"><?php echo e(t('all_data_hint', mb_strtoupper($lang))); ?></p>
                <div class="table-responsive">
                    <table class="table table-sm small align-middle mb-0">
                        <thead>
                        <tr>
                            <th scope="col"><?php echo e(t('field')); ?></th>
                            <th scope="col"><?php echo e(t('value')); ?></th>
                            <th scope="col"><?php echo e(t('source')); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach (['id' => $document['id'] ?? null, 'type' => $type] as $field => $value) { ?>
                            <tr>
                                <td><code><?php echo e($field); ?></code></td>
                                <td><?php echo e($value); ?></td>
                                <td></td>
                            </tr>
                        <?php } ?>
                        <?php foreach (flattenData($data) as $field => $value) {
                            $isTranslated = in_array(explode('.', $field)[0], $translatedFields, true); ?>
                            <tr>
                                <td><code><?php echo e($payloadKey . '.' . $field); ?></code></td>
                                <td class="text-break"><?php echo e(is_array($value) ? implode(', ', $value) : (is_bool($value) ? var_export($value, true) : $value)); ?></td>
                                <td>
                                    <?php if ($isTranslated) { ?>
                                        <span class="badge text-bg-primary"><?php echo e(t('source_translation', mb_strtoupper($lang))); ?></span>
                                    <?php } else { ?>
                                        <span class="badge text-bg-secondary"><?php echo e(t('source_default')); ?></span>
                                    <?php } ?>
                                </td>
                            </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    <?php } ?>

    <?php if ($result !== null) { ?>
        <details class="api-details" open>
            <summary class="text-body-secondary"><i class="bi bi-braces" aria-hidden="true"></i> <?php echo e(t('api_details')); ?></summary>
            <div class="row g-3 mt-1">
                <div class="col-md-6">
                    <h3 class="h6"><?php echo e(t('api_request')); ?></h3>
                    <pre><code><?php echo e($json([
                        'filters' => $searchStruct->getFilters(),
                        'perPage' => $searchStruct->getPerPage(),
                        'locale' => $searchStruct->getLocale(),
                    ])); ?></code></pre>
                </div>
                <div class="col-md-6">
                    <h3 class="h6"><?php echo e(t('api_response')); ?></h3>
                    <pre><code><?php echo e($json(array_diff_key($result->getBody(), ['hits' => true, 'facet_counts' => true]))); ?></code></pre>
                </div>
                <?php if ($rawHit !== null) { ?>
                    <div class="col-md-6">
                        <h3 class="h6"><?php echo e(t('raw_hit_detail')); ?></h3>
                        <pre><code><?php echo e($json($rawHit)); ?></code></pre>
                    </div>
                    <div class="col-md-6">
                        <h3 class="h6"><?php echo e(t('resolved_hit_detail', $result->getLocale())); ?></h3>
                        <pre><code><?php echo e($json($result->getHits()[0])); ?></code></pre>
                    </div>
                <?php } ?>
            </div>
        </details>
    <?php } ?>
</main>

<?php require __DIR__ . '/app/partials/footer.php'; ?>
