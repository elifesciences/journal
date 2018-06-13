#!/usr/bin/env bash
. /opt/smoke.sh/smoke.sh

hostname="${1:-$(hostname)}"
port="${2:-80}"

# retrieve manifest from container, if present
if docker container ls | grep journal_fpm_1; then
    docker cp journal_fpm_1:/srv/journal/build/rev-manifest.json build/
fi

function from_manifest {
    cat build/rev-manifest.json | jq -r ".[\"${1}\"]"
}

smoke_url_ok $hostname:$port/favicon.ico
smoke_url_ok $hostname:$port/$(from_manifest assets/favicons/manifest.json)
smoke_url_ok $hostname:$port/$(from_manifest assets/patterns/css/all.css)
smoke_url_ok $hostname:$port/$(from_manifest assets/images/banners/magazine-1114x336@1.jpg)
smoke_url_ok $hostname:$port/ping
    smoke_assert_body "pong"

if [ "$ENVIRONMENT_NAME" != "ci" ] && [ "$ENVIRONMENT_NAME" != "dev" ]
  then
    set -e
    retry "./status_test.sh $hostname $port" 2
    set +e
    smoke_url_ok $hostname:$port/
fi

smoke_report
