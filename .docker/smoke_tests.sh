#!/usr/bin/env bash
set -e

bin/console --version

assert_fpm "/ping" "pong"
