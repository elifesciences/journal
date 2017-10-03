#!/bin/bash
set -e

rm -rf build/tenon/*.log

for path in $(cat tenon_key_pages.txt)
do
    log_file=$(echo "$path" | tr / _).log
    ./tenon.sh "$path" | tee "build/tenon/$log_file"
done

# will stop on first failure, but all logs are available
for log in $(ls build/tenon/*.log)
do
    ./tenon_check.sh "$log"
done
