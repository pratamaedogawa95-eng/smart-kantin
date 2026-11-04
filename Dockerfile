FROM php:8.2-apache

# Install dependensi sistem yang dibutuhkan PHP & Composer
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql intl zip

# Install Composer secara global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf
ENV PORT=8080

WORKDIR /var/www/html

# Salin semua file proyek
COPY . /var/www/html

# Jalankan composer install untuk mengunduh folder vendor secara otomatis di server
RUN composer install --no-dev --optimize-autoloader

# Buat direktori writable beserta subfoldernya dan berikan izin akses penuh
RUN mkdir -p writable/cache writable/logs writable/session \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 777 writable

EXPOSE 8080