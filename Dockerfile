FROM php:8.2-apache

# Extensions PHP
RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql

# Activer mod_rewrite
RUN a2enmod rewrite

# Config Apache pour le routeur PHP
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' >> /etc/apache2/apache2.conf

COPY . /var/www/html/

# Permissions uploads
RUN mkdir -p /var/www/html/public/uploads/reports \
    && chown -R www-data:www-data /var/www/html/public/uploads

EXPOSE 80
