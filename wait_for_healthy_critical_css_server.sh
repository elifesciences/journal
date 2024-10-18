#!/bin/bash
set -e

critical_css_healthy_test_url="${CRITICAL_CSS_BASE_URL}/resources"
echo "waiting for ${critical_css_healthy_test_url} to be healthy"

timeout="30"

timeout --foreground "$timeout" bash << EOT
    while true; do
        curl "${critical_css_healthy_test_url}" >/dev/null 2> /dev/null && exit 0
        sleep 1
    done
EOT

echo "${critical_css_healthy_test_url} is healthy"
