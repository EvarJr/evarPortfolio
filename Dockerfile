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

# 2. Install PHP extensions needed for CI4 + curl for Cloudinary
RUN docker-php-ext-install \
    intl \
    mysqli \
    pdo_mysql \
    zip \
    gd \
    curl

# 2b. PHP upload/execution limits
RUN echo "upload_max_filesize = 100M" > /usr/local/etc/php/conf.d/uploads.ini \
 && echo "post_max_size = 105M" >> /usr/local/etc/php/conf.d/uploads.ini \
 && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
 && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
 && echo "max_input_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# 3. FIX MPM *after* PHP extensions (order matters!)
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork rewrite

# 4. Set document root to the CI4 public folder
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Copy project files and startup script
WORKDIR /var/www/html
COPY . .
COPY start.sh /start.sh
RUN chmod +x /start.sh

# 7. Install PHP dependencies
RUN composer install --optimize-autoloader --no-scripts --no-interaction

# 8. Set permissions for CI4 writable folders
RUN mkdir -p writable/cache \
             writable/logs \
             writable/session \
             writable/uploads/projects \
    && chmod -R 777 writable/ \
    && chown -R www-data:www-data /var/www/html

EXPOSE 8080

CMD ["/start.sh"]