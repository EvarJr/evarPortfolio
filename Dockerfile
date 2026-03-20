FROM php:8.2-apache

# 1. Install system dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    libonig-dev \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# 2. Install PHP extensions needed for CI4
RUN docker-php-ext-install \
    intl \
    mysqli \
    pdo_mysql \
    zip \
    gd

# 3. FIX MPM *after* PHP extensions (order matters!)
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork rewrite

# 4. Set document root to the CI4 public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Copy project files
WORKDIR /var/www/html
COPY . .

# 7. Install PHP dependencies
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 8. Set permissions for CI4 writable folders
RUN mkdir -p writable/cache \
             writable/logs \
             writable/session \
             writable/uploads \
    && chmod -R 777 writable/ \
    && chown -R www-data:www-data /var/www/html

# 9. Create startup script
# Uses printf to overwrite ports.conf and VirtualHost cleanly at runtime
RUN printf '#!/bin/bash\nset -e\n\n# Fix MPM conflict\na2dismod mpm_event mpm_worker 2>/dev/null || true\na2enmod mpm_prefork 2>/dev/null || true\n\n# Use Railway PORT or default to 8080\nLISTEN_PORT=${PORT:-8080}\necho "Listening on port $LISTEN_PORT"\n\n# Overwrite ports.conf completely (avoids sed running twice bug)\nprintf "Listen %s\\n" "$LISTEN_PORT" > /etc/apache2/ports.conf\n\n# Update VirtualHost port\nsed -i "s/<VirtualHost \\*:[0-9]*>/<VirtualHost *:$LISTEN_PORT>/g" /etc/apache2/sites-available/000-default.conf\n\nexec apache2-foreground\n' > /start.sh \
    && chmod +x /start.sh

EXPOSE 8080

CMD ["/start.sh"]