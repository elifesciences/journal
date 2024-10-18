#!/bin/bash
set -e

echo 'waiting for /resources to be healthy'

timeout="30"

timeout --foreground "$timeout" bash << EOT
    while true; do
        curl ${CRITICAL_CSS_BASE_URL}/resources >/dev/null 2> /dev/null && exit 0
        sleep 1
    done
EOT

echo '/resources is healthy'
