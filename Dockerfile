# ============================================================
# TECNO-MARKET | Imagen de la API + frontend
# PHP 8.2 con Apache, PDO MySQL y mod_rewrite
# ============================================================
FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install pdo pdo_mysql mbstring \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* \
    && a2enmod rewrite \
    && echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html
