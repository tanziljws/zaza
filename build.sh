#!/bin/bash
set -e

# Install PHP extensions including gd and bcmath
install-php-extensions ctype curl dom fileinfo filter hash mbstring openssl pcre pdo session tokenizer xml pdo_mysql redis gd bcmath

# Install composer dependencies
composer install --optimize-autoloader --no-interaction --ignore-platform-req=ext-gd --ignore-platform-req=ext-bcmath || \
composer install --optimize-autoloader --no-interaction

# Build assets
npm ci
npm run build

# Cache Laravel
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

