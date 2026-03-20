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

# 9. Create startup script that:
#    - Fixes MPM at runtime
#    - Sets the correct PORT at runtime (Railway injects $PORT as env var)
RUN printf '#!/bin/bash\nset -e\n\n# Fix MPM conflict\na2dismod mpm_event mpm_worker 2>/dev/null || true\na2enmod mpm_prefork 2>/dev/null || true\n\n# Set port from Railway environment variable (defaults to 80)\nLISTEN_PORT=${PORT:-80}\necho "Listening on port $LISTEN_PORT"\n\n# Update Apache port config at runtime\nsed -i "s/Listen 80/Listen $LISTEN_PORT/g" /etc/apache2/ports.conf\nsed -i "s/<VirtualHost \\*:80>/<VirtualHost *:$LISTEN_PORT>/g" /etc/apache2/sites-available/000-default.conf\n\nexec apache2-foreground\n' > /start.sh \
    && chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]