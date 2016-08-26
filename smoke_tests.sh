#!/usr/bin/env bash
set -ex

[ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)/favicon.ico) == 200 ]
[ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)/assets/css/all.css) == 200 ]
[ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)/status) == 200 ]
#[ $(curl --write-out %{http_code} --silent --output /dev/null $(hostname)) == 200 ]
