#!/bin/bash
set -e

vendor/bin/phpunit --log-junit build/phpunit.xml
