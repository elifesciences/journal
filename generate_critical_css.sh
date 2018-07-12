#!/usr/bin/env bash
set -e

folder="build/critical-css"
temporary_css="${folder}/temp.css"

function finish {
    echo "Stopping containers..."
    docker-compose -f docker-compose.yml -f docker-compose.critical-css.yml down
    echo "Done"
}

function clean {
    find ${folder} -name "*.css" -type f -delete
}

trap finish EXIT

echo "Cleaning existing critical CSS..."
clean
touch ${temporary_css} # Something has to exist for a Docker COPY to work
echo "Done"

echo "Starting containers..."
finish &> /dev/null
docker-compose -f docker-compose.yml -f docker-compose.critical-css.yml up --build --detach critical_css
echo "Done"

echo "Generating critical CSS..."
docker-compose -f docker-compose.yml -f docker-compose.critical-css.yml exec -T critical_css node_modules/.bin/gulp critical-css:generate
echo "Done"

echo "Checking critical CSS..."
docker-compose -f docker-compose.yml -f docker-compose.critical-css.yml exec -T critical_css ./check_critical_css.sh
echo "Done"

echo "Copying critical CSS..."
rm ${temporary_css}
docker cp journal_critical_css_1:build/critical-css/. build/critical-css/
echo "Done"
