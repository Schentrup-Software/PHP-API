#!/bin/bash

sudo pecl install ast;
echo "extension=ast.so" | sudo tee -a /usr/local/etc/php/conf.d/docker-php-ext-sodium.ini;
composer install
composer dump-autoload
