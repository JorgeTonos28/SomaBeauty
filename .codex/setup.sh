#!/usr/bin/env bash
set -e

# 1. Herramientas básicas
apt-get update -qq
apt-get install -y \
  git curl unzip gnupg \
  php-cli php-mbstring php-xml php-sqlite3 php-pdo-sqlite \
  php-pdo php-dom php-json php-tokenizer php-curl php-zip php-bcmath

# 2. Composer
curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 3. Node.js (v20)
curl -fsSL https://deb.nodesource.com/setup_20.x | bash -
apt-get install -y nodejs

# 4. Dependencias del proyecto
composer install --optimize-autoloader
npm install
npm run build

# 5. Prepara entorno de testing con SQLite en memoria
cp .env .env.testing || true
echo "APP_ENV=testing" >> .env.testing
echo "DB_CONNECTION=sqlite" >> .env.testing
echo "DB_DATABASE=:memory:" >> .env.testing

# 6. Ejecutar tests
vendor/bin/phpunit --testdox || true
