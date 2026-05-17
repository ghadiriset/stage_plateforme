FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev \
    libmariadb-dev \
    libzip-dev \
    zip unzip \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

RUN a2enmod rewrite

# Apache config propre
RUN echo '<VirtualHost *:80>\n\
    DocumentRoot /var/www/html\n\
    <Directory /var/www/html>\n\
        AllowOverride All\n\
        Require all granted\n\
    </Directory>\n\
</VirtualHost>' > /etc/apache2/sites-available/000-default.conf

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

RUN mkdir -p /var/www/html/public/uploads/reports \
    && chown -R www-data:www-data /var/www/html/public/uploads

EXPOSE 80
