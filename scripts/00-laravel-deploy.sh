#!/usr/bin/env bash
echo "Running composer"
cp /etc/secrets/.env .env
composer global require hirak/prestissimo
composer install --no-dev --working-dir=/var/www/html
# Install Node.js and npm if not detected automatically
if ! command -v npm &> /dev/null
then
    echo "npm not found, installing Node.js"
    curl -sL https://deb.nodesource.com/setup_lts.x | bash -
    apt-get install -y nodejs
fi
echo "npm build"
npm install
npm run build

echo "Clearing caches..."
php artisan optimize:clear

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..."
php artisan migrate --force

echo "done deploying"