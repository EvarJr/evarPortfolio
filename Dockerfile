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

# 2. Install PHP extensions
RUN docker-php-ext-install \
    intl \
    mysqli \
    pdo_mysql \
    zip \
    gd

# 3. FIX: Resolve the "More than one MPM loaded" error
# We explicitly disable mpm_event and enable mpm_prefork
RUN a2dismod mpm_event || true && a2enmod mpm_prefork

# 4. Enable Apache mod_rewrite (required for CI4 routing)
RUN a2enmod rewrite

# 5. Set document root to CI4 public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 6. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 7. Copy project files
WORKDIR /var/www/html
COPY . .

# 8. Install PHP dependencies
# Note: We run this AFTER copying files so composer.json is present
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