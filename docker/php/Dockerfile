FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql

# Set document root to /public
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf \
    /etc/apache2/apache2.conf

# Enable API alias
COPY api.conf /etc/apache2/conf-available/api.conf
RUN a2enconf api
