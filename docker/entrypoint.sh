#!/bin/sh
set -e

# Render (and similar platforms) assign a dynamic port via $PORT and expect
# the app to bind to it. Local dev (docker-compose) doesn't set $PORT, so
# this defaults to 80, matching the previous hardcoded behavior exactly.
PORT="${PORT:-80}"
sed -ri "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

# Opt-in only (set RUN_MIGRATIONS_ON_BOOT=true in the hosting platform's env
# vars) so local dev's existing manual `artisan migrate` workflow is
# unaffected by default.
if [ "$RUN_MIGRATIONS_ON_BOOT" = "true" ]; then
    php artisan migrate --force
fi

exec "$@"
