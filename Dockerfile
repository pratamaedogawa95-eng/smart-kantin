FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql intl zip

RUN a2enmod rewrite

# Memperbaiki error More than one MPM loaded
RUN a2dismod mpm_event && a2enmod mpm_prefork

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
ENV PORT=8080

COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html/writable /var/www/html/public

EXPOSE 8080