#!/bin/sh

set -e

echo "🚀 Starting Laravel application..."

# Wait for database to be ready
echo "⏳ Waiting for database..."
while ! nc -z mysql 3306; do
  sleep 1
done
echo "✅ Database is ready!"

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
  echo "🔑 Generating APP_KEY..."
  php artisan key:generate
fi

# Run migrations
echo "📊 Running database migrations..."
php artisan migrate --force

# Run database seeding if in development
if [ "$APP_ENV" = "development" ] || [ "$APP_ENV" = "local" ]; then
  echo "🌱 Seeding database..."
  php artisan db:seed --force || true
fi

# Clear caches
echo "🧹 Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "✨ Laravel ready!"
echo

# Execute the main command
exec "$@"
