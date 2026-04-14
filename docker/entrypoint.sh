#!/bin/sh
set -e

# Define the app path clearly to avoid "file not found" errors
APP_PATH="/var/www/html"

echo "🚀 Starting DevFlow..."

# ── 1. Wait for MySQL (Simplified check) ───────────────────
echo "⏳ Waiting for MySQL..."
until php -r "try { new PDO('mysql:host=${DB_HOST};dbname=${DB_DATABASE}', '${DB_USERNAME}', '${DB_PASSWORD}'); } catch (Exception \$e) { exit(1); }" 2>/dev/null; do
    sleep 2
done
echo "✅ Database connected."

# ── 2. Run Critical Laravel Tasks ──────────────────────────
# We use full paths here to ensure it never fails
cd $APP_PATH

echo "🔑 Ensuring App Key..."
php artisan key:generate --force --no-interaction

echo "🗄️  Running Migrations..."
php artisan migrate --force --no-interaction

# ── 3. Storage Permissions ─────────────────────────────────
# Alpine uses 'chown' better than 'chmod' for Laravel
chown -R www:www storage bootstrap/cache

echo "✅ DevFlow is ready to go!"

# ── 4. Start the Process ───────────────────────────────────
# This executes whatever command is in your Dockerfile (CMD) or docker-compose
# In docker/entrypoint.sh, change the last line to:
exec "$@"