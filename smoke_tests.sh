#!/usr/bin/env bash
. /opt/smoke.sh/smoke.sh


bin/console --version --env=$ENVIRONMENT_NAME

smoke_url_ok $(hostname)/favicon.ico
smoke_url_ok $(hostname)/assets/css/all.css
smoke_url_ok $(hostname)/images/banners/magazine-hi-res.jpg
smoke_url_ok $(hostname)/ping
    smoke_assert_body "pong"

if [ "$ENVIRONMENT_NAME" != "ci" ] && [ "$ENVIRONMENT_NAME" != "dev" ]
  then
    curl -v $(hostname)/status
    smoke_url_ok $(hostname)/status
    smoke_url_ok $(hostname)/
fi

smoke_report
