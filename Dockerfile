# Use PHP 8.3 with Apache as base image
FROM php:8.3-apache

# Install required packages
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy project files
COPY hesabixCore/ /var/www/html/hesabixCore/
COPY webUI/ /var/www/html/webUI/
COPY public_html/ /var/www/html/public_html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Enable Apache modules
RUN a2enmod rewrite

# Copy Apache configuration
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Install PHP dependencies
WORKDIR /var/www/html/hesabixCore
RUN composer install --no-interaction --optimize-autoloader

# Install Node.js dependencies and build web UI
WORKDIR /var/www/html/webUI
RUN npm install && npm run build-only

# Return to main directory
WORKDIR /var/www/html

# Expose ports
EXPOSE 80

# Run command
CMD ["apache2-foreground"] 