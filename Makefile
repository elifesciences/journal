ifeq (${TARGET},)
TARGET := dev
endif

DOCKER_COMPOSE = docker compose

.PHONY: build dev stop clean

build:
	$(DOCKER_COMPOSE) build

dev: build
	${DOCKER_COMPOSE} up 

stop:
	$(DOCKER_COMPOSE) down

clean:
	$(DOCKER_COMPOSE) down --volumes --remove-orphans

