#!/bin/bash
set -e

maximum=${1:-3}

for attempt in $(seq 1 $maximum); do
    echo "Attempt: $attempt"
    if ! node_modules/.bin/gulp; then
        echo "Gulp failure, retrying"
        continue
    else
        echo "Gulp succeeded"
        break
    fi
done
echo "Giving up after $attempt consecutive failures"
