#!/bin/sh
set -e

echo "=== Billing Platform Backend V3 — Entrypoint ==="

# 1. Laravel can run entirely from injected environment variables.
DOTENV_PATH=/var/www/html/.env
if [ ! -f "$DOTENV_PATH" ]; then
    if [ -n "${APP_ENV:-}" ]; then
        echo "[entrypoint] .env not found; using injected environment variables."
    elif [ -w /var/www/html ] && [ -r /var/www/html/.env.example ]; then
        echo "[entrypoint] .env not found, copying the development template..."
        cp /var/www/html/.env.example "$DOTENV_PATH"
    else
        echo "[entrypoint] .env not found; using injected environment variables."
    fi
fi

# 2. Prepare runtime directories before Artisan writes logs/cache files.
ensure_runtime_permissions() {
    if [ "$(id -u)" -ne 0 ]; then
        for directory in /var/www/html/storage /var/www/html/bootstrap/cache; do
            if [ ! -w "$directory" ]; then
                echo "[entrypoint] Runtime directory is not writable: $directory" >&2
                return 1
            fi
        done
        return 0
    fi

    mkdir -p \
        /var/www/html/storage/logs \
        /var/www/html/storage/framework/cache \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/views \
        /var/www/html/bootstrap/cache
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
    find /var/www/html/storage /var/www/html/bootstrap/cache -type d -exec chmod 775 {} +
    find /var/www/html/storage /var/www/html/bootstrap/cache -type f -exec chmod 664 {} +
}

ensure_runtime_permissions

# 3. Wait for PostgreSQL to be ready
echo "[entrypoint] Waiting for PostgreSQL at ${DB_HOST:-db_v3}:${DB_PORT:-5432}..."
until php -r "
    \$host = getenv('DB_HOST') ?: getenv('MASTER_V3_DB_HOST') ?: 'db_v3';
    \$port = getenv('DB_PORT') ?: getenv('MASTER_V3_DB_PORT') ?: '5432';
    \$db   = getenv('DB_DATABASE') ?: getenv('MASTER_V3_DB_DATABASE') ?: 'master_v3';
    \$user = getenv('DB_USERNAME') ?: getenv('MASTER_V3_DB_USERNAME') ?: 'postgres';
    \$pass = getenv('DB_PASSWORD') ?: getenv('MASTER_V3_DB_PASSWORD') ?: '';
    try {
        \$pdo = new PDO(\"pgsql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo 'ok';
    } catch (\Exception \$e) {
        echo 'fail';
    }
" | grep -q '^ok$'; do
    echo "[entrypoint] PostgreSQL not ready, retrying in 2s..."
    sleep 2
done
echo "[entrypoint] PostgreSQL is ready."

# 4. Run V3 migrations (creates schemas in master_v3: auth, core, fiscal, integration, platform)
echo "[entrypoint] Running V3 migrations..."
php artisan v3:migrate --force

# 5. Run V3 seeders (MasterV3DatabaseSeeder: tenant, company, establishments, sequences, IVA, CONSUMIDOR FINAL, products)
echo "[entrypoint] Checking if V3 seeders are needed..."
SEED_COUNT=$(php -r "
    \$host = getenv('DB_HOST') ?: getenv('MASTER_V3_DB_HOST') ?: 'db_v3';
    \$port = getenv('DB_PORT') ?: getenv('MASTER_V3_DB_PORT') ?: '5432';
    \$db   = getenv('DB_DATABASE') ?: getenv('MASTER_V3_DB_DATABASE') ?: 'master_v3';
    \$user = getenv('DB_USERNAME') ?: getenv('MASTER_V3_DB_USERNAME') ?: 'postgres';
    \$pass = getenv('DB_PASSWORD') ?: getenv('MASTER_V3_DB_PASSWORD') ?: '';
    try {
        \$pdo = new PDO(\"pgsql:host=\$host;port=\$port;dbname=\$db\", \$user, \$pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        echo \$pdo->query('SELECT COUNT(*) FROM platform.tenants')->fetchColumn();
    } catch (\Exception \$e) {
        echo '0';
    }
" 2>/dev/null || echo "0")
if [ "$SEED_COUNT" = "0" ]; then
    echo "[entrypoint] Empty database detected, running V3 seeders..."
    php artisan v3:seed --force
else
    echo "[entrypoint] Database already seeded ($SEED_COUNT tenants), skipping seeders."
fi

# 6. Cache config, routes, views (production optimization)
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "[entrypoint] Caching config, routes, views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
else
    echo "[entrypoint] APP_ENV is not production, skipping caches."
fi

# Restore ownership before PHP-FPM workers run as www-data.
ensure_runtime_permissions

echo "=== Entrypoint complete. Starting PHP-FPM... ==="

# 7. Execute the main command (php-fpm)
exec "$@"
