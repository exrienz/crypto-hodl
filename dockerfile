# Use official PHP 8.1 with Apache
FROM php:8.1-apache

# Install required PHP extensions, MySQL client, Python3, and pip
RUN apt-get update && apt-get install -y \
    default-mysql-client \
    python3 \
    python3-pip \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && pip3 install beautifulsoup4 --break-system-packages \
    && pip3 install requests --break-system-packages

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY src/ /var/www/html
COPY script/ /var/www/script/

# Set correct permissions
# Give www-data ownership and appropriate permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chown -R www-data:www-data /var/www/script \
    && chmod -R 755 /var/www/script \
    && chmod +x /var/www/script/tp2txt.py

# Expose port 80
EXPOSE 80

# Start Apache in foreground
CMD ["apache2-foreground"]