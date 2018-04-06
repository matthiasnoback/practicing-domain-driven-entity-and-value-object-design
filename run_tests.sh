#!/usr/bin/env bash

vendor/bin/phpunit --testsuite unit -v \
    && vendor/bin/phpunit --testsuite integration -v \
    && vendor/bin/behat --suite acceptance -vvv
