#!/usr/bin/env bash
set -e

vendor/bin/phpunit --log-junit build/phpunit.xml
vendor/bin/behat --tags '~wip' --format junit --format pretty

