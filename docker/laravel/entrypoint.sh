#!/usr/bin/env bash
set -e

cd /var/www/html

set_env_value() {
    local key="$1"
    local value="$2"

    if grep -q "^${key}=" .env; then
        sed -i "s|^${key}=.*|${key}=${value}|" .env
    else
        printf '\n%s=%s\n' "${key}" "${value}" >> .env
    fi
}

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
    set_env_value "APP_NAME" '"Video Monitor Hub"'
    set_env_value "APP_URL" "http://localhost:8000"
    set_env_value "DB_CONNECTION" "pgsql"
    set_env_value "DB_HOST" "database"
    set_env_value "DB_PORT" "5432"
    set_env_value "DB_DATABASE" "video_monitor"
    set_env_value "DB_USERNAME" "postgres"
    set_env_value "DB_PASSWORD" "secret"
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

if [ -f artisan ] && ! grep -q '^APP_KEY=base64:' .env; then
    php artisan key:generate --force
fi

if [ -f package.json ] && [ ! -f node_modules/.bin/vite ]; then
    npm install
fi

if [ -f package.json ] && [ ! -f public/build/manifest.json ]; then
    npm run build
fi

exec "$@"
