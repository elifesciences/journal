#!/usr/bin/env bash
. /opt/smoke.sh/smoke.sh

hostname="${1:-$(hostname)}"
port="${2:-80}"

checks=$(curl -v "$hostname:$port/status" | grep check__name)
echo "$checks"
good_checks=$(echo "$checks" | grep -o ✔ | wc -l)
bad_checks=$(echo "$checks" | grep -o ✘ | wc -l)
if [ "$bad_checks" -ne 0 ]; then
    echo "Not all status checks are ok"
    exit 2
elif [ "$good_checks" -eq 0 ]; then
    echo "There is not even one status check"
    exit 3
else
    echo "All checks are ok"
fi
