#!/bin/bash

rm -rf tests/logs examples/logs logs

php -v

rm -rf vendor composer.lock

composer --no-cache update -o \
    && ./vendor/bin/psalm --show-info=true \
    && ./vendor/bin/phpunit tests