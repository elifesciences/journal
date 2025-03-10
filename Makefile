DOCKER_COMPOSE = docker-compose
TEST = test/
BRANCH = master

.PHONY: build dev prod stop clean test feature-test lint lint-fix check update-patterns

update-patterns:
	composer require elife/patterns:dev-$(BRANCH)

update-api-sdk:
	composer require elife/api-sdk:dev-master

build: vendor
	$(DOCKER_COMPOSE) build

vendor: composer.json composer.lock
	composer install
	@touch vendor

dev: build vendor
	$(DOCKER_COMPOSE) up

prod: build vendor
	API_URL=https://prod--gateway.elifesciences.org $(DOCKER_COMPOSE) up

stop:
	$(DOCKER_COMPOSE) down

clean:
	$(DOCKER_COMPOSE) down --volumes --remove-orphans
	rm -rf vendor

test: vendor
	APP_ENV=ci $(DOCKER_COMPOSE) run --rm app vendor/bin/phpunit $(TEST) $(OPTIONS)

lint: vendor
	.ci/phpcs

lint-fix: vendor
	vendor/bin/phpcbf --standard=phpcs.xml.dist --warning-severity=0 -p app/ bin/ src/ web/ test/

check: test lint
