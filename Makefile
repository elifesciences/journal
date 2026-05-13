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
	CRITICAL_CSS_JSON=prod API_URL=https://api.prod.elifesciences.org $(DOCKER_COMPOSE) up

stop:
	$(DOCKER_COMPOSE) down

clean:
	$(DOCKER_COMPOSE) down --volumes --remove-orphans --rmi all
	rm -rf vendor
	@echo "If you are still not seeing what you expect after cleaning, you may need to run 'docker system prune'"

test: vendor
	APP_ENV=ci $(DOCKER_COMPOSE) run --rm app vendor/bin/phpunit $(TEST) $(OPTIONS)

lint: vendor
	.ci/phpcs

lint-fix: vendor
	vendor/bin/phpcbf --standard=phpcs.xml.dist --warning-severity=0 -p app/ bin/ src/ web/ test/

check: test lint
