#!/bin/sh

set -eu

port="${PORT:-8080}"

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY is required. Generate one before starting the container." >&2
    exit 1
fi

# Keep exactly one process model enabled. This is repeated at startup so a
# platform layer or cached image cannot leave Apache with conflicting MPMs.
a2dismod -f mpm_event mpm_worker mpm_prefork >/dev/null 2>&1 || true
rm -f \
    /etc/apache2/mods-enabled/mpm_*.load \
    /etc/apache2/mods-enabled/mpm_*.conf
a2enmod mpm_prefork >/dev/null

enabled_mpm_count="$(find /etc/apache2/mods-enabled -maxdepth 1 -type l -name 'mpm_*.load' | wc -l | tr -d ' ')"
if [ "$enabled_mpm_count" -ne 1 ] || [ ! -L /etc/apache2/mods-enabled/mpm_prefork.load ]; then
    echo "ERROR: Apache must have only mpm_prefork enabled." >&2
    exit 1
fi

sed -ri "s/^Listen [0-9]+$/Listen ${port}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${port}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    attempt=1
    until php artisan migrate --force --no-interaction; do
        if [ "$attempt" -ge 30 ]; then
            echo "ERROR: Database migrations failed after ${attempt} attempts." >&2
            exit 1
        fi

        echo "Database unavailable; retrying migration (${attempt}/30)..." >&2
        attempt=$((attempt + 1))
        sleep 2
    done
fi

if [ "${RUN_SEEDER:-false}" = "true" ]; then
    if [ -z "${ADMIN_EMAIL:-}" ] || [ -z "${ADMIN_PASSWORD:-}" ]; then
        echo "ERROR: ADMIN_EMAIL and ADMIN_PASSWORD are required when RUN_SEEDER=true." >&2
        exit 1
    fi

    php artisan db:seed --force --no-interaction
fi

php artisan storage:link --force >/dev/null 2>&1 || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

exec docker-php-entrypoint "$@"
