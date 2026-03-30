FROM php:8.2-cli

# Install MySQL driver
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Set working directory
WORKDIR /var/www/html

# Copy all project files
COPY . .

# Make startup script executable
RUN chmod +x start.sh

CMD ["bash", "start.sh"]