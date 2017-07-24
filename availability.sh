#!/usr/bin/env bash
set -e

if [ "$#" -lt 1 ]; then
    echo "Performs a series of uniquely identifiable requests to journal's /status, logging result and time"
    echo "Usage: $0 HOSTNAME [PATH]"
    echo "Example: $0 prod--journal.elifesciences.org"
    echo "Example: $0 prod--journal.elifesciences.org /ping"
    exit 1
fi

hostname=$1
page=${2:-/status}
log_file="${hostname}.log"

rm -f "${log_file}"
while true
do
    current_time=$(date +%H:%M:%S)
    uuid=$(uuidgen)
    status_code=$(curl --write-out '%{http_code}\n' "https://${hostname}${page}?request=$uuid" -o /dev/null)
    echo "${current_time},${uuid},${status_code}" >> "${log_file}"
done

