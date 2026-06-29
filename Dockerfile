FROM php:8.2-apache

# Install ekstensi MySQL
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Aktifkan mod_rewrite
RUN a2enmod rewrite

# Salin project
COPY . /var/www/html/

# Permission
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Set working directory
WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]