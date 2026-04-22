#!/bin/sh
# Fix storage/cache permissions after bind-mount overwrites Dockerfile chown
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
exec php-fpm "$@"
