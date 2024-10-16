DOCKER_COMPOSE = docker-compose

.PHONY: build dev stop clean test feature-test lint check

build:
	$(DOCKER_COMPOSE) build

vendor:
	composer install

dev: build vendor
	${DOCKER_COMPOSE} up 

exploratory-test-from-prod: build vendor
	API_URL=https://prod--gateway.elifesciences.org ${DOCKER_COMPOSE} up

clean:
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

test:
	APP_ENV=ci $(DOCKER_COMPOSE) run --rm app vendor/bin/phpunit
	APP_ENV=ci $(DOCKER_COMPOSE) down --volumes

feature-test:
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml up --build --detach
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml run --rm ci .ci/behat
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml down --volumes

lint: vendor
	.ci/phpcs

check: test lint
