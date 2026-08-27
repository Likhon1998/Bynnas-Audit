#!/usr/bin/env bash
set -euo pipefail

# Run from the live app directory, e.g.:
#   cd /home/bynnsuou/audit.bynnas.com && bash deploy.sh
#
# Or after pulling the Git clone:
#   cd /home/bynnsuou/repositories/Bynnas-Audit && git pull
#   rsync ... then run this in audit.bynnas.com

APP_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$APP_DIR"

echo "==> App: $APP_DIR"

if [[ ! -f .env ]]; then
  echo "Missing .env — create it first, then re-run."
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

echo "==> migrate + seed"
# First deploy / broken partial DB: wipe then migrate cleanly.
php artisan db:wipe --force
php artisan migrate --force
php artisan db:seed --class=RolePermissionSeeder --force

echo "==> storage link + permissions"
php artisan storage:link || true
chmod -R ug+rwx storage bootstrap/cache || true

echo "==> optimize"
php artisan optimize:clear || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Done. Document root must be: $APP_DIR/public"
echo "==> Login: admin@bynnasaudit.com / 12345678 (change after login)"
