#!/usr/bin/env bash
echo "Running composer"
cp /etc/secrets/.env .env
composer global require hirak/prestissimo
composer install --no-dev --working-dir=/var/www/html

echo "npm build"

# Function to install npm for Debian-based systems
install_npm_debian() {
  echo "Detected Debian-based system."
  curl -fsSL https://deb.nodesource.com/setup_16.x | bash -
  apt-get install -y nodejs
}

# Function to install npm for Arch-based systems
install_npm_arch() {
  echo "Detected Arch-based system."
  pacman -Syu --noconfirm nodejs npm
}

# Function to detect Linux distribution
if [ -f /etc/os-release ]; then
  . /etc/os-release
  case $ID in
    debian|ubuntu|linuxmint)
      install_npm_debian
      ;;
    arch|manjaro)
      install_npm_arch
      ;;
    alpine)
    apk add --update nodejs npm
      ;;
    *)
      echo "Unsupported Linux distribution: $ID"
      exit 1
      ;;
  esac
else
  echo "Cannot detect the Linux distribution. Exiting."
  exit 1
fi

npm install
npm run build


echo "Clearing caches..."
php artisan optimize:clear

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "Running migrations..."
php artisan migrate:refresh --seed

echo "done deploying"