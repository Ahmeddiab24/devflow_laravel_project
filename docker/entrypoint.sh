#!/bin/sh
##############################################################################
#  DevFlow — Docker Entrypoint
#  Runs on container start. Handles first-boot setup and graceful restarts.
##############################################################################
set -e

echo "🚀 DevFlow entrypoint starting..."

# ── Wait for MySQL ─────────────────────────────────────────────────────────
echo "⏳ Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null; do
    echo "   MySQL not ready, retrying in 2s..."
    sleep 2
done
echo "✅ MySQL is ready"

# ── Wait for Redis ─────────────────────────────────────────────────────────
echo "⏳ Waiting for Redis..."
until redis-cli -h "${REDIS_HOST:-redis}" -p "${REDIS_PORT:-6379}" ping 2>/dev/null | grep -q PONG; do
    echo "   Redis not ready, retrying in 2s..."
    sleep 2
done
echo "✅ Redis is ready"

# ── Generate App Key (first boot only) ────────────────────────────────────
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    echo "🔑 Generating application key..."
    php artisan key:generate --force
fi

# ── Run Migrations ─────────────────────────────────────────────────────────
echo "🗄️  Running migrations..."
php artisan migrate --force --no-interaction

# ── Seed on first boot (if DB is empty) ───────────────────────────────────
USER_COUNT=$(php artisan tinker --execute="echo App\\Models\\User::count();" 2>/dev/null | tail -1 || echo "0")
if [ "$USER_COUNT" = "0" ]; then
    echo "🌱 Seeding database..."
    php artisan db:seed --force --no-interaction
fi

# ── Cache configuration for production ────────────────────────────────────
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Caching config, routes and views..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache
else
    echo "🔧 Development mode - skipping cache"
fi

# ── Fix storage permissions ────────────────────────────────────────────────
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo "✅ DevFlow ready!"

# ── Execute main process ───────────────────────────────────────────────────
exec "$@"
