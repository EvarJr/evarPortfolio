#!/bin/bash
set -e

# Fix MPM conflict
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Use Railway PORT or default to 8080
LISTEN_PORT=${PORT:-8080}
echo "Listening on port $LISTEN_PORT"

# Overwrite ports.conf completely
echo "Listen $LISTEN_PORT" > /etc/apache2/ports.conf

# Update VirtualHost port
sed -i "s/<VirtualHost \*:[0-9]*>/<VirtualHost *:$LISTEN_PORT>/g" /etc/apache2/sites-available/000-default.conf

# Create uploads directory and symlink into public so Apache serves files directly
mkdir -p /var/www/html/writable/uploads/projects
chmod -R 777 /var/www/html/writable/uploads
ln -sfn /var/www/html/writable/uploads /var/www/html/public/uploads

exec apache2-foreground