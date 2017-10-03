#!/bin/bash
set -e

if [ "$#" -ne 1 ]; then
    echo "Usage: ./tenon.sh PATH"
    echo "Example: ./tenon.sh /community"
    echo "You can override the URL to check with the SCHEME, HOST and PORT environment variables"
    exit 1
fi

if [ -z "$TENON_API_KEY" ]; then
    echo "You need to set a TENON_API_KEY environment variable to be able to run this script"
    exit 2
fi  

scheme="${SCHEME:-https}"
host="${HOSTNAME:-$(hostname)}"
port="${PORT:-8443}"
path="$1"
url="${scheme}://${host}:${port}${path}"
curl -v \
    -X POST \
    -H "Cache-Control: no-cache" \
    -d "url=$url&key=${TENON_API_KEY}" https://tenon.io/api/ | jq .

