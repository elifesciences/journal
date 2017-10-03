#!/bin/bash
set -eo pipefail

rm -rf build/tenon/*.log

while read path
do
    log_file=$(echo "$path" | tr / _).log
    ./tenon.sh "$path" | tee "build/tenon/$log_file"
done < tenon_key_pages.txt

# will stop on first failure, but all logs are available
for log in build/tenon/*.log
do
    ./tenon_check.sh "$log"
done
