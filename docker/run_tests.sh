#!/usr/bin/env bash

set -eu

docker-compose run --rm php ./run_tests.sh
