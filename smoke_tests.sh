#!/usr/bin/env bash
. /opt/smoke.sh/smoke.sh

function from_manifest {
    cat build/rev-manifest.json | jq -r ".[\"${1}\"]"
}

bin/console --version --env=$ENVIRONMENT_NAME

smoke_url_ok $(hostname)/favicon.ico
smoke_url_ok $(hostname)/$(from_manifest assets/favicons/manifest.json)
smoke_url_ok $(hostname)/$(from_manifest assets/patterns/css/all.css)
smoke_url_ok $(hostname)/$(from_manifest assets/images/banners/magazine-1114x336@1.jpg)
smoke_url_ok $(hostname)/ping
    smoke_assert_body "pong"

if [ "$ENVIRONMENT_NAME" != "ci" ] && [ "$ENVIRONMENT_NAME" != "dev" ]
  then
    checks=$(curl -v $(hostname)/status | grep check__name)
    echo $checks
    good_checks=$(echo $checks | grep -o ✔ | wc -l)
    bad_checks=$(echo $checks | grep -o ✘ | wc -l)
    if [ "$bad_checks" -ne 0 ]; then
        echo "Not all status checks are ok"
        exit 2
    elif [ "$good_checks" -eq 0 ]; then
        echo "There is not even one status check"
        exit 3
    else
        echo "All checks are ok"
    fi
    smoke_url_ok $(hostname)/
fi

smoke_report
