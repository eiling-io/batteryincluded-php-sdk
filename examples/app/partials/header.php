<?php

declare(strict_types=1);

/**
 * Page head and navigation bar, shared by all pages of the demo shop.
 *
 * @var string                    $lang
 * @var string                    $query       search term shown in the search field
 * @var string                    $pageTitle   prefix of the document title
 * @var string                    $homeUrl     target of the logo
 * @var callable(string): string  $languageUrl url of the current page in another language
 */
?>
<!doctype html>
<html lang="<?php echo e($lang); ?>" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($pageTitle !== '' ? $pageTitle . ' · ' . t('title') : t('title')); ?></title>
    <script>
        // set the theme before rendering to avoid flickering
        (() => {
            let theme = null;
            try { theme = localStorage.getItem('bi-demo-theme'); } catch (e) {}
            theme ??= matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            document.documentElement.dataset.bsTheme = theme;
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?php echo e(asset('assets/app.css')); ?>" rel="stylesheet">
</head>
<body>

<header class="navbar navbar-expand sticky-top bg-body border-bottom shadow-sm py-2">
    <div class="container-xxl gap-3">
        <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold" href="<?php echo e($homeUrl); ?>">
            <i class="bi bi-battery-charging fs-4 text-primary" aria-hidden="true"></i>
            <span class="d-none d-md-inline">BatteryIncluded</span>
        </a>

        <form class="search flex-grow-1 position-relative" action="index.php" method="get" role="search" data-suggest-url="api/suggest.php">
            <input type="hidden" name="lang" value="<?php echo e($lang); ?>">
            <div class="input-group">
                <span class="input-group-text bg-body-tertiary border-end-0"><i class="bi bi-search" aria-hidden="true"></i></span>
                <input class="form-control border-start-0 bg-body-tertiary" type="search" name="q" value="<?php echo e($query); ?>"
                       placeholder="<?php echo e(t('search_placeholder')); ?>" aria-label="<?php echo e(t('search')); ?>"
                       autocomplete="off" role="combobox" aria-expanded="false" aria-controls="suggest-list" aria-autocomplete="list">
                <button class="btn btn-primary" type="submit"><?php echo e(t('search')); ?></button>
            </div>
            <div class="suggest dropdown-menu w-100 shadow p-0" id="suggest-list" role="listbox"
                 data-label-suggestions="<?php echo e(t('suggestions')); ?>" data-label-products="<?php echo e(t('products')); ?>"
                 data-label-all="<?php echo e(t('show_all_results', '%s')); ?>"></div>
        </form>

        <div class="dropdown">
            <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?php echo e(t('language')); ?>">
                <span aria-hidden="true"><?php echo DEMO_LANGUAGES[$lang]['flag']; ?></span>
                <span class="d-none d-sm-inline"><?php echo e(mb_strtoupper($lang)); ?></span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <?php foreach (DEMO_LANGUAGES as $code => $language) { ?>
                    <li>
                        <a class="dropdown-item<?php echo $code === $lang ? ' active' : ''; ?>" href="<?php echo e($languageUrl($code)); ?>" hreflang="<?php echo e($code); ?>" lang="<?php echo e($code); ?>">
                            <span aria-hidden="true"><?php echo $language['flag']; ?></span> <?php echo e($language['label']); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>

        <button class="btn btn-outline-secondary" type="button" data-theme-toggle title="<?php echo e(t('theme_toggle')); ?>" aria-label="<?php echo e(t('theme_toggle')); ?>">
            <i class="bi bi-moon-stars theme-icon-light" aria-hidden="true"></i>
            <i class="bi bi-sun theme-icon-dark" aria-hidden="true"></i>
        </button>
    </div>
</header>
