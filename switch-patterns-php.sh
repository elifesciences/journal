#!/bin/bash
set -e

if [ "$#" != 1 ]; then
    echo "Usage: ./switch-patterns-php.sh PATTERNS_PHP_BRANCH"
    echo "Example: ./switch-patterns-php.sh update_pattern_library/PR-621"
    exit 1
fi

branch="$1"

composer install
cat composer.json | jq '.require["elife/patterns"] = "dev-'$branch'"' > composer-switch-patterns-php.json
COMPOSER=composer-switch-patterns-php.json composer update elife/patterns --no-interaction
./retrying-gulp.sh
