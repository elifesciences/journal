#!/usr/bin/env bash
set -e

folder="build/critical-css"

function finish {
    echo "Stopping containers..."
    docker-compose -f docker-compose.yml -f docker-compose.critical-css.yml down
    echo "Done"
}

trap finish EXIT

echo "Building containers..."
finish &> /dev/null
docker-compose -f docker-compose.yml -f docker-compose.critical-css.yml build
echo "Done"

echo "Starting containers..."
docker-compose -f docker-compose.yml -f docker-compose.critical-css.yml up --detach
echo "Done"

echo "Generating critical CSS..."
docker-compose -f docker-compose.yml -f docker-compose.critical-css.yml exec -T critical_css node_modules/.bin/gulp critical-css:generate
echo "Done"

echo "Cleaning existing critical CSS..."
find ${folder} -name "*.css" -type f -delete
echo "Done"

echo "Copying critical CSS..."
docker cp journal_critical_css_1:build/critical-css/. build/critical-css/
echo "Done"

echo "Checking critical CSS..."
./check_critical_css.sh
echo "Done"
