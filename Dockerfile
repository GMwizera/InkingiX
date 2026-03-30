FROM php:8.2-apache

# Install MySQL driver and other extensions
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Set Apache document root to project root
ENV APACHE_DOCUMENT_ROOT /var/www/html

# Fix permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80