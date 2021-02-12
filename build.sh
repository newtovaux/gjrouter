#!/bin/bash

rm tests/logs/*

composer dump-autoload -o \
    && ./vendor/bin/psalm --show-info=true \
    && ./vendor/bin/phpunit tests