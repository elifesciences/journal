#!/bin/bash
set -e

folder="build/critical-css/"
file_list=$(find "$folder" -name '*.css')

if [ "$file_list" == "" ]; then
    echo "No files were generated in $folder"
    exit 2
fi

echo "Files generated:"
echo "$file_list"

echo "$file_list" | while read filename; do
    echo "Checking $filename is a non-empty file..."
    test -s "$filename"
done
