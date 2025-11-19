FROM php:7.4-apache

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libpng-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql mysqli pdo_mysql zip mbstring gd

# Enable mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application source
COPY . /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html
