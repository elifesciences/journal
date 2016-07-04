#!/bin/bash
set -e

[ $(curl --write-out %{http_code} --silent --output /dev/null localhost:1240/favicon.ico) == 200 ]
[ $(curl --write-out %{http_code} --silent --output /dev/null localhost:1240/status) == 200 ]
