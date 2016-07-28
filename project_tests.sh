#!/usr/bin/env bash
set -e

proofreader app/ bin/ features/ src/ test/ web/
vendor/bin/phpunit --log-junit build/phpunit.xml
vendor/bin/behat --tags '~wip' --format junit --format pretty

