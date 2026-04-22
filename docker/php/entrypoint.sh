#!/bin/sh
# Fix storage/cache permissions after bind-mount overwrites Dockerfile chown
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Auto-generate APP_KEY directly if .env exists but key is missing
if [ -f /var/www/.env ] && ! grep -qE '^APP_KEY=.+' /var/www/.env; then
    KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    sed -i "s|^APP_KEY=.*|APP_KEY=${KEY}|" /var/www/.env
    echo "APP_KEY generated."
fi

exec "$@"
