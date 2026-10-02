#!/usr/bin/env sh
set -eu

mkdir -p /var/run/php /run/nginx
chown -R nginx:nginx /var/run/php /run/nginx /var/log/nginx

# Run composer install if composer.json exists
if [ -f "/var/www/html/composer.json" ]; then
    composer install --no-interaction
fi

# Create drush symlink if it doesn't exist
if [ ! -L "/usr/local/sbin/drush" ]; then
    ln -s /var/www/html/vendor/bin/drush /usr/local/sbin/drush
fi

# Render Nginx config from template with runtime-configurable internal port.
envsubst '${WEB_INTERNAL_PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf

php-fpm -D
exec nginx -g 'daemon off;'
