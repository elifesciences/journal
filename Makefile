DOCKER_COMPOSE = docker-compose

.PHONY: build dev stop clean test feature-test lint check

build:
	$(DOCKER_COMPOSE) build

vendor:
	composer install

dev: build
	${DOCKER_COMPOSE} up 

stop:
	$(DOCKER_COMPOSE) down

clean:
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

test:
	APP_ENV=ci $(DOCKER_COMPOSE) run app vendor/bin/phpunit

feature-test:
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml up --build --detach
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml run ci .ci/behat
	docker-compose -f docker-compose.yml -f docker-compose.ci.yml down -v

lint: vendor
	.ci/phpcs

check: test lint
