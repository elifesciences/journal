#!/usr/bin/env bash
set -e

rm -f build/*.xml

echo "cache:clear"
bin/console cache:clear --env=test --no-warmup

echo "security:check"
bin/console security:check

echo "proofreader"
proofreader app/ bin/ src/ web/
proofreader --no-phpcpd features/ test/

echo "PHPUnit tests"
vendor/bin/phpunit --log-junit build/phpunit.xml

echo "Behat tests"
vendor/bin/behat --strict --tags '~wip' --format junit --format progress
