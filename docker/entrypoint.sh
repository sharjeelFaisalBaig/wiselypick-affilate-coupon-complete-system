#!/bin/sh
set -e

# Render (and most container platforms) inject $PORT at runtime and expect
# the container to listen on it — it isn't known at image build time, so
# Apache's port is rewritten here on every boot instead. Defaults to 80 for
# a plain `docker run` with no PORT set.
PORT="${PORT:-80}"
sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:[0-9]+>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link
fi

# The DB may still be starting up alongside this container on first boot —
# retry briefly instead of crash-looping immediately.
attempt=1
until php artisan migrate --force; do
    if [ "$attempt" -ge 10 ]; then
        echo "migrate: giving up after ${attempt} attempts" >&2
        exit 1
    fi
    echo "migrate: database not ready yet (attempt ${attempt}), retrying in 3s..." >&2
    attempt=$((attempt + 1))
    sleep 3
done

exec "$@"
