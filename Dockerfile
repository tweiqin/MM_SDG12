FROM php:8.0-apache

# Install mysqli extension and mysql-client
RUN apt-get update && apt-get install -y default-mysql-client \
    && docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy application source code to web root
COPY . /var/www/html/

# Set permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www/html/

# Expose port 80
EXPOSE 80
