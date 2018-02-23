#!/usr/bin/env bash
set -e

echo "cache:clear"
rm -rf var/cache

echo "security:check"
vendor/bin/security-checker security:check

echo "proofreader"
proofreader app/ bin/ src/ web/
proofreader --no-phpcpd features/ test/

echo "PHPUnit tests"
rm -rf build/phpunit/
vendor/bin/fastest --no-interaction --xml phpunit.xml.dist "vendor/bin/phpunit --log-junit build/phpunit/{n}.xml {};"

echo "Behat tests"
rm -rf build/behat/
vendor/bin/behat --list-scenarios --tags '~wip' | vendor/bin/fastest --no-interaction "JARNAIZ_JUNIT_OUTPUTDIR=build/behat JARNAIZ_JUNIT_FILENAME={n}.xml vendor/bin/behat --strict --format junit {};" -vv --no-errors-summary

# Tenon disabled as flaky and not (yet) used much
# echo "Tenon tests"
# ./tenon_key_pages.sh
