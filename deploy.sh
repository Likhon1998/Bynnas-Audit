#!/usr/bin/env bash
set -euo pipefail

# Safe update deploy (does NOT wipe DB).
# Run on the LIVE app directory:
#   cd ~/audit.bynnas.com && bash deploy.sh
#
# Typical full flow:
#   cd ~/repositories/Bynnas-Audit && git fetch origin && git reset --hard origin/main
#   rsync -a --delete \
#     --exclude '.env' --exclude 'storage/' --exclude 'bootstrap/cache/' \
#     --exclude 'node_modules/' --exclude '.git/' --exclude 'vendor/' \
#     ~/repositories/Bynnas-Audit/ ~/audit.bynnas.com/
#   cd ~/audit.bynnas.com && bash deploy.sh

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$APP_DIR"

echo "==> App: $APP_DIR"

if [[ ! -f .env ]]; then
  echo "Missing .env — create it first, then re-run."
  exit 1
fi

if [[ ! -f public/index.php ]]; then
  echo "ERROR: public/index.php missing. Document root / deploy path is wrong."
  exit 1
fi

# Ensure root entry exists when docroot is project root (cPanel/LiteSpeed).
if [[ ! -f index.php ]]; then
  printf '%s\n' '<?php' "require __DIR__.'/public/index.php';" > index.php
fi

# Vite "hot" file must never exist on production (breaks CSS/JS).
rm -f public/hot

if [[ ! -f public/build/manifest.json ]]; then
  echo "ERROR: public/build/manifest.json missing."
  echo "On your PC run: npm run build"
  echo "Then upload/sync the public/build folder to the server."
  exit 1
fi

if [[ -f composer.phar ]]; then
  COMPOSER=(php composer.phar)
elif command -v composer >/dev/null 2>&1; then
  COMPOSER=(composer)
elif [[ -x /opt/cpanel/composer/bin/composer ]]; then
  COMPOSER=(/opt/cpanel/composer/bin/composer)
else
  echo "Composer not found. Installing local composer.phar..."
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php composer-setup.php
  php -r "unlink('composer-setup.php');"
  COMPOSER=(php composer.phar)
fi

echo "==> composer install"
"${COMPOSER[@]}" install --no-dev --optimize-autoloader --no-interaction

if [[ ! -f vendor/autoload.php ]]; then
  echo "ERROR: vendor/autoload.php still missing after composer install."
  exit 1
fi

echo "==> migrate (no wipe)"
php artisan migrate --force

echo "==> storage link + permissions"
php artisan storage:link || true
# Web server needs traverse (+x) on dirs and read on files (rsync can break this).
find "$APP_DIR" -type d -exec chmod 755 {} \; 2>/dev/null || true
find "$APP_DIR" -type f -exec chmod 644 {} \; 2>/dev/null || true
chmod -R ug+rwx storage bootstrap/cache || true
chmod 755 "$APP_DIR" "$APP_DIR/public" || true
# Make sure entry scripts are readable/executable enough for LiteSpeed.
chmod 644 "$APP_DIR/index.php" "$APP_DIR/public/index.php" "$APP_DIR/.htaccess" "$APP_DIR/public/.htaccess" || true

echo "==> optimize"
php artisan optimize:clear || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Done."
echo "==> Prefer document root: $APP_DIR/public"
echo "==> Fallback root index.php is present for project-root docroots."
