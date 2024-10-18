#!/bin/bash
set -e

critical_css_healthy_test_url="${CRITICAL_CSS_BASE_URL}/resources"
echo "waiting for ${critical_css_healthy_test_url} to be healthy"

timeout="30"

timeout --foreground "$timeout" bash << EOT
    while true; do
        curl -o /dev/null -s -w "%{http_code}\n" --fail "${critical_css_healthy_test_url}" && exit 0
        sleep 1
    done
EOT

echo "${critical_css_healthy_test_url} is healthy"
