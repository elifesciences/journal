PROJECT_NAME = patterns-php

vendor:
	composer install

.PHONY: test
test: vendor
	vendor/bin/phpunit

.PHONY: phpcs
phpcs: vendor
	vendor/bin/phpcs --standard=phpcs.xml.dist --warning-severity=0 -p bin src tests

.PHONY: lint
lint: phpcs
	./syntax-check.sh

.PHONY: update-pattern-library
update-pattern-library:
	bin/update

.PHONY: build
build:
	$(if $(PHP_VERSION),,$(error PHP_VERSION make variable needs to be set))
	docker buildx build --build-arg=PHP_VERSION=$(PHP_VERSION) -t $(PROJECT_NAME):$(PHP_VERSION) .

.PHONY: lint-ci
lint-ci: build
	docker run --rm $(PROJECT_NAME):$(PHP_VERSION) bash -c 'vendor/bin/phpcs --standard=phpcs.xml.dist --warning-severity=0 -p bin src tests'
	docker run --rm $(PROJECT_NAME):$(PHP_VERSION) bash -c 'find src tests -name '*.php' | xargs -L1 php -l'

.PHONY: test-ci
test-ci: build
	docker run --rm $(PROJECT_NAME):$(PHP_VERSION) bash -c 'vendor/bin/phpunit'

.PHONY: check
check: test lint
