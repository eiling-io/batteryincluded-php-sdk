MAKEFLAGS += --warn-undefined-variables
MAKEFLAGS += --no-builtin-rules

.PHONY: help
.PHONY: help
help:       ## Shows the help
help:
	@printf "\033[33mUsage:\033[0m\n  make TARGET\n\n\033[32m#\n# Commands\n#---------------------------------------------------------------------------\033[0m\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | awk 'BEGIN {FS = ":"}; {printf "\033[33m%s:\033[0m%s\n", $$1, $$2}'

.PHONY: ddev-quality
ddev-quality:     ## executes all quality scripts in the running ddev container
ddev-quality:
	ddev exec -s web "PHP_CS_FIXER_IGNORE_ENV=true ./tools/php-cs-fixer fix --config=.php-cs-fixer.php"
	ddev exec -s web "./tools/phpstan analyse -l 5 ./src/ ./tests/"
	ddev exec -s web "./vendor/bin/phpunit --stop-on-failure --stop-on-error --coverage-html=./build/artifacts/html-coverage"


.PHONY: ddev-cs
ddev-cs:     ## executes code style scripts in the running ddev container
ddev-cs:
	ddev exec -s web "PHP_CS_FIXER_IGNORE_ENV=true ./tools/php-cs-fixer fix --config=.php-cs-fixer.php"

.PHONY: ddev-phpstan
ddev-phpstan:     ## executes phpstan scripts in the running ddev container
ddev-phpstan:
	ddev exec -s web "./tools/phpstan analyse -l 5 ./src/ ./tests/"

.PHONY: ddev-unit
ddev-unit:     ## executes php unit in the running ddev container
ddev-unit:
	ddev exec -s web "./vendor/bin/phpunit --stop-on-failure --stop-on-error --coverage-html=./build/artifacts/html-coverage"


