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

# 2. FIX: Explicitly disable conflicting MPM modules
# This prevents the "More than one MPM loaded" error
RUN a2dismod mpm_event || true && a2enmod mpm_prefork

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

# Fixed the sed command (using double quotes to allow ENV variable expansion)
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
# Added --no-dev for production deployment
RUN composer install --optimize-autoloader --no-scripts --no-interaction --no-dev

# 9. Create CI4 writable folders and set permissions
# It is better to give ownership to the www-data user
RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 writable/

# Railway uses the PORT environment variable. 
# We don't hardcode EXPOSE 80 because Railway assigns a random port.
# Apache by default listens on 80; Railway maps its $PORT to your container's port.

CMD ["apache2-foreground"]