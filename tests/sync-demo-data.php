<?php

declare(strict_types=1);

/*
 * Full sync of the demo data (720 products, 10 blog posts) into the collection of COLLECTION / APIKEY.
 *
 * The live tests only sync products, so the CI runs this script at the end to leave the collection in the state
 * the demo shop expects. A full import deletes every other document of the collection.
 *
 * Usage: COLLECTION=customer.xyz APIKEY=... php tests/sync-demo-data.php
 */

use BatteryIncludedSdk\Service\SyncService;
use BatteryIncludedSdkTests\Helper;

require_once __DIR__ . '/../vendor/autoload.php';

if (!getenv('COLLECTION') || !getenv('APIKEY')) {
    fwrite(STDERR, "COLLECTION and APIKEY have to be set.\n");
    exit(1);
}

$documents = array_merge(Helper::generateProducts(20), Helper::generateBlogs(10));
$response = (new SyncService(Helper::getApiClient()))->syncFullElements(...$documents);

$results = $response->getBody();
$failed = array_values(array_filter($results, static fn ($result) => !($result['success'] ?? false)));

printf("Synced %d of %d documents (http %d).\n", count($results) - count($failed), count($documents), $response->getStatusCode());

if ($response->getStatusCode() >= 300 || count($results) !== count($documents) || $failed !== []) {
    fwrite(STDERR, 'Full sync failed: ' . json_encode(array_slice($failed, 0, 10)) . PHP_EOL);
    exit(1);
}
