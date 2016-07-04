#!/bin/bash
set -e

[ $(curl --write-out %{http_code} --silent --output /dev/null localhost/favicon.ico) == 200 ]
[ $(curl --write-out %{http_code} --silent --output /dev/null localhost/status) == 200 ]
