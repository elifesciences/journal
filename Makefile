DOCKER_COMPOSE = docker-compose
TEST = test/
BRANCH = master
ELIGIBILITY_JSON_S3_URI =

.PHONY: build dev prod stop clean test feature-test lint lint-fix check update-patterns eligibility-json-from-s3 eligibility-json-push

update-patterns:
	composer require elife/patterns:dev-$(BRANCH)

update-api-sdk:
	composer require elife/api-sdk:dev-master

build: vendor
	$(DOCKER_COMPOSE) build

vendor: composer.json composer.lock
	composer install
	@touch vendor

eligibility.json: eligibility.json.dist
	cp eligibility.json.dist eligibility.json

web/eligibility.json: eligibility.json bin/strip-eligibility-json.php
	php bin/strip-eligibility-json.php eligibility.json > web/eligibility.json

eligibility-json-from-s3:
ifeq ($(ELIGIBILITY_JSON_S3_URI),)
	$(error Set ELIGIBILITY_JSON_S3_URI to the S3 path of the real eligibility.json, e.g. make eligibility-json-from-s3 ELIGIBILITY_JSON_S3_URI=s3://bucket/path/eligibility.json)
endif
	aws s3 cp $(ELIGIBILITY_JSON_S3_URI) eligibility.json
	php bin/strip-eligibility-json.php eligibility.json > web/eligibility.json

eligibility-json-push: web/eligibility.json
	$(DOCKER_COMPOSE) cp web/eligibility.json web:/srv/journal/web/eligibility.json

dev: build vendor web/eligibility.json
	$(DOCKER_COMPOSE) up

prod: build vendor web/eligibility.json
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
