#!/bin/sh
# Fix storage/cache permissions after bind-mount overwrites Dockerfile chown
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Auto-generate APP_KEY if .env exists but key is missing
if [ -f /var/www/.env ] && ! grep -q '^APP_KEY=.\+' /var/www/.env; then
    php /var/www/artisan key:generate --force
fi

exec "$@"
