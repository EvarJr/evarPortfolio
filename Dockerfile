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

# 3. FIX: Kill the "More than one MPM loaded" error
# We physically delete the conflicting event and worker modules 
# and force Apache to use the prefork module.
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf \
    && a2enmod mpm_prefork rewrite

# 4. FIX: Port configuration for Railway
# Railway assigns a random port; we must tell Apache to listen on it.
RUN sed -i "s/Listen 80/Listen \${PORT}/g" /etc/apache2/ports.conf \
    && sed -i "s/<VirtualHost \*:80>/<VirtualHost *:\${PORT}>/g" /etc/apache2/sites-available/000-default.conf

# 5. Set document root to the CI4 public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Copy project files
WORKDIR /var/www/html
COPY . .

# 8. Install PHP dependencies
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 9. Set permissions for CI4 writable folders
RUN mkdir -p writable/cache \
             writable/logs \
             writable/session \
             writable/uploads \
    && chmod -R 777 writable/ \
    && chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]