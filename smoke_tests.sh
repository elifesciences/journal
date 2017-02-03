#!/usr/bin/env bash
set -ex

php bin/console
[ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)/favicon.ico) == 200 ]
[ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)/assets/css/all.css) == 200 ]
[ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)/images/banners/magazine-hi-res.jpg) == 200 ]
[ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)/ping) == 200 ]

if [ "$ENVIRONMENT_NAME" != "ci" ]
  then
    [ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)/status) == 200 ]
    [ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)) == 200 ]
fi
