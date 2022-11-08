#!/bin/bash

sudo sed -i \
        -e "s/^;extension=intl/extension=intl/" \
        -e "s/^;extension=pdo_mysql/extension=pdo_mysql/" \
        -e "s/^;extension=pdo_sqlite/extension=pdo_sqlite/" \
        "/etc/php/php.ini";