#!/bin/bash

rm -rf tests/logs examples/logs logs

composer dump-autoload -o \
    && ./vendor/bin/psalm --show-info=true \
    && ./vendor/bin/phpunit tests