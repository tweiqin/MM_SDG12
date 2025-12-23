FROM php:8.0-apache

# Install mysqli extension, mysql-client, and curl
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    libcurl4-openssl-dev \
    && docker-php-ext-install mysqli curl \
    && docker-php-ext-enable mysqli curl

# Copy application source code to web root
COPY . /var/www/html/

# Set permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www/html/

# Expose port 80
EXPOSE 80
