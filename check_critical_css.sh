#!/bin/bash
set -e

folder="build/critical-css/"
expected_list=$(jq -r '. | keys[] | "'$folder'\(.).css"' < critical-css.json | sort)
actual_list=$(find "$folder" -name '*.css' | sort)

echo "Expected list of files in $folder:"
echo "$expected_list"
echo "Actual list of files in $folder:"
echo "$actual_list"
diff -u <(echo "$expected_list") <(echo "$actual_list")

echo "Files generated:"
echo "$actual_list"

echo "$actual_list" | while read filename; do
    echo "Checking $filename is a non-empty file..."
    test -s "$filename"
done
