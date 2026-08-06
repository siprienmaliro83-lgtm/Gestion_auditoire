# ======================================
# Étape 1 : Build des assets Vite
# ======================================
FROM node:22 AS node_builder

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .

RUN npm run build

# ======================================
# Étape 2 : Laravel + Apache
# ======================================
FROM php:8.3-apache

# Installer les dépendances système
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    curl \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
        exif \
        bcmath \
        intl \
        gd \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Activer mod_rewrite
RUN a2enmod rewrite

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier tout le projet Laravel
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copier les assets Vite compilés
COPY --from=node_builder /app/public/build ./public/build

# Permissions Laravel
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 storage bootstrap/cache

# Configurer Apache pour pointer vers public/
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!/var/www/html/public!g' \
    /etc/apache2/apache2.conf \
    /etc/apache2/conf-available/*.conf

# Optimiser Laravel
RUN php artisan config:clear \
    && php artisan cache:clear \
    && php artisan route:clear \
    && php artisan view:clear \
    && php artisan config:cache

# Exposer le port
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]