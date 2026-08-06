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
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_sqlite \
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

# Copier les fichiers Composer
COPY composer.json composer.lock ./

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copier le projet Laravel
COPY . .

# Copier les fichiers compilés par Vite
COPY --from=node_builder /app/public/build ./public/build

# Créer la base SQLite si elle n'existe pas
RUN mkdir -p database && touch database/database.sqlite

# Optimiser Composer
RUN composer dump-autoload --optimize

# Donner les permissions nécessaires
RUN chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

# Configurer Apache pour utiliser le dossier public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' \
    /etc/apache2/sites-available/*.conf

# Exposer le port
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]