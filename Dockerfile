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

# 2. FORCE FIX: Physically remove all MPM configs and only enable prefork
# This is the "brute force" fix for the AH00534 error
RUN rm -f /etc/apache2/mods-enabled/mpm_event.load /etc/apache2/mods-enabled/mpm_event.conf \
    && rm -f /etc/apache2/mods-enabled/mpm_worker.load /etc/apache2/mods-enabled/mpm_worker.conf \
    && a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork

# 3. Install PHP extensions
RUN docker-php-ext-install \
    intl \
    mysqli \
    pdo_mysql \
    zip \
    gd

# 4. Enable Apache mod_rewrite
RUN a2enmod rewrite

# 5. Set document root to CI4 public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf
RUN sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Allow .htaccess overrides
RUN echo '<Directory /var/www/html/public>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

# 6. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Copy project files
WORKDIR /var/www/html
COPY . .

# 8. Install PHP dependencies
RUN composer install --optimize-autoloader --no-scripts --no-interaction --no-dev

# 9. Create CI4 writable folders and set permissions
RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 writable/

EXPOSE 80

CMD ["apache2-foreground"]