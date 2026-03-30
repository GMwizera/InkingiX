FROM php:8.2-cli

# Install MySQL driver
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Use PHP built-in server (no Apache needed)
CMD php -S 0.0.0.0:$PORT -t /var/www/html