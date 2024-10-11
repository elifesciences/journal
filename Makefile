ifeq (${TARGET},)
TARGET := dev
endif

DOCKER_COMPOSE = docker compose

.PHONY: build dev stop clean test

build:
	$(DOCKER_COMPOSE) build

dev: build
	${DOCKER_COMPOSE} up 

stop:
	$(DOCKER_COMPOSE) down

clean:
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

test:
	APP_ENV=ci $(DOCKER_COMPOSE) run app vendor/bin/phpunit
