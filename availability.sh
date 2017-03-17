#!/usr/bin/env bash
set -e

if [ ! "$#" -eq 1 ]; then
    echo "Performs a series of uniquely identifiable requests to journal's /status, logging result and time"
    echo "Usage: $0 prod--journal.elifesciences.org"
    exit 1
fi

hostname=$1
log_file="${hostname}.log"

rm -f "${log_file}"
while true
do
    current_time=$(date +%H:%M:%S)
    uuid=$(uuidgen)
    status_code=$(curl --write-out '%{http_code}\n' "https://${hostname}/status?request=$uuid" -o /dev/null)
    echo "${current_time},${uuid},${status_code}" >> "${log_file}"
done

