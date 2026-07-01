FROM php:8.2-apache

# 1. Install MySQL extensions for PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo_mysql

# 2. Copy custom Apache config to use the dynamic Render port
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# 3. Copy your entire project structure into the container
COPY . /var/www/html/

# 4. Set appropriate permissions for file uploads
RUN chown -R www-data:www-data /var/www/html/imagenes/subidas \
    && chmod -R 755 /var/www/html/imagenes/subidas

# 5. Enable Apache rewrite module
RUN a2enmod rewrite

EXPOSE 80
