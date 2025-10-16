<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>BatterIncluded Demo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>
<?php

use BatteryIncludedSdk\Client\ApiClient;
use BatteryIncludedSdk\Client\CurlHttpClient;
use BatteryIncludedSdk\Service\BrowseSearchStruct;
use BatteryIncludedSdk\Service\BrowseService;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/credentials.php';
$apiClient = new ApiClient(
    new CurlHttpClient(),
    'https://api.batteryincluded.io/api/v1/collections/',
    $collection,
    $apiKey
);

$_GET['per_page'] = (int) ($_GET['per_page'] ?? 10);
$_GET['page'] = (int) ($_GET['page'] ?? 10);

$browseService = new BrowseService($apiClient);
$searchStruct = new BrowseSearchStruct();
$searchStruct->setPerPage($_GET['per_page']);
$searchStruct->setPage($_GET['page']);
$searchStruct->addFilters($_GET['f'] ?? []);
$searchStruct->setQuery($_GET['q'] ?? ''); // f[][]=Gold
$result = $browseService->browse($searchStruct);

?>
<div class="container-fluid">

    <h1>BatteryIncludedSdk</h1>
    <div class="row">
        <div class="col-md-3">
            <form method="get" action="index.php">
                <input class="form-control" type="text" name="q" value="<?php echo $_GET['q']; ?>" />
                <select class="form-select" aria-label="Default select example" name="per_page" onchange="this.form.submit()">
                    <option value="10">Pro Seite</option>
                    <option value="10" <?php echo $_GET['per_page'] === 10 ? 'selected' : ''; ?>>10</option>
                    <option value="20" <?php echo $_GET['per_page'] === 20 ? 'selected' : ''; ?>>20</option>
                    <option value="30" <?php echo $_GET['per_page'] === 30 ? 'selected' : ''; ?>>30</option>
                </select>
                <select class="form-select" aria-label="Default select example" name="page" onchange="this.form.submit()">
                    <option value="1">Seite</option>
                    <?php
                        foreach (range(1, $result->getPages()) as $page) {
                            echo '<option value="' . $page . '" ' . ($result->getPage() === $page ? 'selected' : '') . '>' . $page . '</option>';
                        }
?>
                </select>
                <?php
foreach ($result->getFacets() as $facet) {
    echo '<h3>' . $facet->getFieldLabel() . '</h3>';
    foreach ($facet->getValues() as $valueName => $valueObject) {
        $isChecked = $valueObject->isChecked();
        $count = $valueObject->getCount();
        $value = $valueObject->getName();
        echo '<div class="form-check">';
        echo '<input class="form-check-input" type="checkbox" name="f[' . $facet->getFieldName() . '][]" value="' . $value . '" id="' . $facet->getFieldName() . '_' . $value . '" ' . ($isChecked ? 'checked' : '') . ' onchange="this.form.submit()">';
        echo '<label class="form-check-label" for="' . $facet->getFieldName() . '_' . $value . '">';
        echo $value . ' ' . $facet->getFieldUnit() . ' (' . $count . ')';
        echo '</label>';
        echo '</div>';
    }
}
?>
            </form>
        </div>

        <div class="col-md-9">
            <div class="row row-cols-4 row-cols-md-4 g-4">
                <?php
foreach ($result->getHits() as $hit) {
    $data = $hit['document'];
    echo '
        <div class="mb-3 col">
          <img src="' . $data['imageUrl'] . '" class="card-img-top" alt="Artikelbild">
          <div class="card-body">
            <h5 class="card-title">' . $data['name'] . '</h5>
            <p class="card-text">' . $data['description'] . '</p>
            <a href="#" class="btn btn-primary">Zum Artikel</a>
          </div>
        </div>
        ';
}
?>
            </div>
        </div>
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
        crossorigin="anonymous"></script>
</body>
</html>