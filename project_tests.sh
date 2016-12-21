#!/usr/bin/env bash
set -e

rm -f build/*.xml
bin/console security:check
proofreader app/ bin/ features/ src/ web/
proofreader --no-phpcpd test/
vendor/bin/phpunit --log-junit build/phpunit.xml
vendor/bin/behat --strict --tags '~wip' --format junit --format pretty

