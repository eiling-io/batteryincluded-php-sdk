---
title: Development
nav_order: 10
---

# Development
{: .no_toc }

1. TOC
{:toc}

Contributions are welcome. This page describes how to run the quality tools before you open a pull request.

## Setup

```bash
ddev start
ddev composer install
```

All commands below are also available without DDEV if PHP and Composer are installed locally.

## Tests

```bash
make ddev-unit
# or
./vendor/bin/phpunit
```

The suite contains unit tests and live tests against the BatteryIncluded api. The live tests sync the test data of
`tests/Helper.php` into the collection given by the `COLLECTION` and `APIKEY` environment variables. Set them in a
local `phpunit.xml` (copy of `phpunit.xml.dist`, ignored by git) or as secrets in CI.

{: .warning }
Use a dedicated test collection, the live tests overwrite its documents.

The live tests sync products only. To restore the complete demo data (720 products and 10 blog posts) run a full
sync, the CI does this at the end of every run:

```bash
COLLECTION=customer.my-test-collection APIKEY=... php tests/sync-demo-data.php
```

## Code style

```bash
make ddev-cs
# or
./tools/php-cs-fixer fix --config=.php-cs-fixer.php
```

The rule set enables `mb_str_functions`. On PHP ≥ 8.4 the fixer therefore replaces `trim()` with `mb_trim()`; the SDK
provides a polyfill for older versions, so the result is identical on all supported PHP versions.

## Static analysis

```bash
make ddev-phpstan
# or
./tools/phpstan analyse -l 5 ./src/ ./tests/
```

## All checks

```bash
make ddev-quality
```

The CI runs code style and PHPStan on PHP 8.2, 8.3, 8.4 and 8.5 without secrets. The tests talk to the live API and
run in the job `tests (live api)` with the secrets of the GitHub environment `testing`: a maintainer has to approve the
run (required reviewers of the environment) after reviewing the code. Pushes to `main` use the environment
`testing-main` instead, which has the same secrets, no reviewers and is limited to the branch `main`. The job runs the tests on all PHP versions one
after another and finally restores the demo data with `tests/sync-demo-data.php`, also when the tests failed. Pull
requests from forks get no secrets.

## Documentation

This documentation lives in `docs/` and is published with GitHub Pages. Preview it locally with Jekyll:

```bash
cd docs
bundle init && bundle add github-pages jekyll-remote-theme
bundle exec jekyll serve
```
