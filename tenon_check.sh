#!/bin/bash
set -e

if [ "$#" -ne 1 ]; then
    echo "Checks a log from tenon.sh"
    echo "Usage: ./tenon_check.sh LOG_FILE"
    echo "Example: ./tenon_check.sh build/tenon/_.log"
    exit 1
fi

log="$1"

api_errors=$(jq '.apiErrors | length' < "$log")
if [ "$api_errors" -ne 0 ]; then
    echo "apiErrors is not empty in $log"
    exit 2
fi

result_set=$(jq '.resultSet | length' < "$log")
if [ "$result_set" -ne 0 ]; then
    echo "resultSet is not empty ($result_set issues) in $log"
    exit 3
fi

echo "$log is green"
