DOCKER_COMPOSE = docker-compose
TEST = test/
BRANCH = master

.PHONY: build dev prod stop clean test feature-test lint check update-patterns

update-patterns:
	composer require elife/patterns:dev-$(BRANCH)

update-api-sdk:
	composer require elife/api-sdk:dev-master

build: vendor
	$(DOCKER_COMPOSE) build

vendor:
	composer install

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

feature-test:
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml up --build --detach
ifdef FEATURE
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml run --rm ci vendor/bin/behat $(FEATURE)
else
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml run --rm ci .ci/behat
endif
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml down --volumes

lint: vendor
	.ci/phpcs
