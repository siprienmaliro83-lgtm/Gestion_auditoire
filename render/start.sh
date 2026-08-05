#!/usr/bin/env bash
set -e

# ============================================================
# Script de démarrage (exécuté à chaque déploiement sur Render)
# Base de données : PostgreSQL externe (DATABASE_URL fournie par
# Render) — plus de base SQLite locale ni de disque persistant.
# - Migrations exécutées (idempotentes)
# - Seed uniquement si la base est vide
# - Apache configuré sur le port fourni par Render ($PORT)
# ============================================================

PORT="${PORT:-80}"

echo "[render] Démarrage de l'application (PostgreSQL)..."

# 1. Dossiers de stockage Laravel
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chown -R www-data:www-data storage bootstrap/cache

# 2. Migrations (Laravel ne rejoue que celles non appliquées)
echo "[render] Migrations..."
php artisan migrate --force

# 3. Seed uniquement si la table des rôles est vide
if php -r "
    require 'vendor/autoload.php';
    \$app = require 'bootstrap/app.php';
    \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    exit(App\Models\Role::exists() ? 0 : 1);
"; then
    echo "[render] Base déjà initialisée, seed ignoré."
else
    echo "[render] Base vide, lancement du seed..."
    php artisan db:seed --force
fi

# 4. Lien de stockage public
php artisan storage:link --force || true

# 5. Cache de configuration (performance, ne bloque pas en cas d'échec)
php artisan config:cache || true

# 6. Apache : écouter sur le port Render
if [ "$PORT" != "80" ]; then
    echo "[render] Configuration Apache sur le port $PORT..."
    sed -i "s/^Listen 80$/Listen $PORT/" /etc/apache2/ports.conf
    sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf
fi

# 7. Démarrage d'Apache
echo "[render] Lancement d'Apache..."
exec apache2-foreground
