# ============================================================
# Dockerfile — Gestion des auditoires (Laravel 12 / PHP 8.2)
# Déploiement Render : Runtime "Docker"
# ============================================================
FROM php:8.2-apache

# --- Extensions PHP + outils nécessaires ---
RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip-dev \
        libpq-dev \
        unzip \
        git \
        curl \
    && docker-php-ext-install pdo pdo_sqlite sqlite3 pgsql pdo_pgsql mbstring zip \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && rm -rf /var/lib/apt/lists/*

# --- Apache : mod_rewrite + headers ---
RUN a2enmod rewrite headers

# --- Apache : DocumentRoot -> public/ (configuration Laravel) ---
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

# --- Application ---
WORKDIR /var/www/html
COPY . .

# --- Dépendances Composer (production uniquement) ---
RUN composer install --no-dev --optimize-autoloader --prefer-dist --no-interaction --no-progress

# --- Permissions d'écriture Laravel ---
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# --- Port HTTP (Render redirige vers $PORT) ---
EXPOSE 80

CMD ["bash", "/var/www/html/render/start.sh"]
