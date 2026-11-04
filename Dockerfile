FROM php:8.2-apache

# Install dependensi sistem yang dibutuhkan PHP & PostgreSQL
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libicu-dev \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql intl zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer secara global
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf
RUN sed -ri -e 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

ENV PORT=8080

WORKDIR /var/www/html

# Salin semua file proyek
COPY . /var/www/html

# Jalankan composer install untuk mengunduh vendor di server
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Buat direktori writable beserta subfoldernya dan atur izin akses
RUN mkdir -p writable/cache writable/logs writable/session writable/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/writable

# Script penyesuaian PORT dinamis Railway saat container dimulai
CMD sh -c "sed -i \"s/Listen 80/Listen \${PORT:-8080}/g; s/Listen 8080/Listen \${PORT:-8080}/g\" /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf && apache2-foreground"
