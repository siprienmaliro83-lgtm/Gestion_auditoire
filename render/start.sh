#!/usr/bin/env bash
set -e

# ------------------------------------------------------------
# Script de démarrage pour Render (Web Service, plan gratuit)
# - SQLite est placé sur le disque persistant (/var/data)
# - Les migrations sont exécutées (idempotentes)
# - Le seed n'a lieu que si la base est vide
# - Le serveur PHP intégré écoute sur $PORT fourni par Render
# ------------------------------------------------------------

DB_PATH="${DB_DATABASE:-/var/data/database.sqlite}"

echo "[render] Database path: $DB_PATH"

# 1. S'assurer que le dossier du disque persistant existe
mkdir -p "$(dirname "$DB_PATH")"

# 2. Créer le fichier SQLite s'il n'existe pas
if [ ! -f "$DB_PATH" ]; then
    echo "[render] Création du fichier SQLite..."
    touch "$DB_PATH"
fi

# 3. S'assurer que les dossiers de stockage existent et sont inscriptibles
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs
chmod -R 775 storage bootstrap/cache || true

# 4. Migrations (idempotentes : Laravel suit celles déjà appliquées)
echo "[render] Lancement des migrations..."
php artisan migrate --force

# 5. Seed uniquement si la table des rôles est vide
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

# 6. Lien de stockage public (ne casse rien s'il existe déjà)
php artisan storage:link --force || true

# 7. Vider les caches de configuration pour un démarrage propre
php artisan config:clear >/dev/null 2>&1 || true

# 8. Démarrage du serveur
echo "[render] Démarrage du serveur sur le port ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
