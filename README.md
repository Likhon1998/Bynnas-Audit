# Bynnas-Audit

Bynnas Audit — Laravel app with login, dashboard, and a dynamic audit organogram.

## Login

- URL: `/login`
- Email: `admin@bynnasaudit.com`
- Password: `12345678`

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```
