# Atelier — Luxury Menswear Storefront

A headless e-commerce site: a **Laravel 13** API + admin, and a **Nuxt 4** server-rendered storefront.

```
clothing/
├── backend/    Laravel 13 — REST API, Filament admin at /admin
└── frontend/   Nuxt 4 — SSR storefront
```

## Requirements

| | Version | Notes |
|---|---|---|
| PHP | **8.4+** | Laravel 13 requires `^8.3`. Laragon ships 8.4.6 — select it under *Menu → PHP → Version*. |
| Composer | 2.8+ | |
| Node | 22.19+ | |
| MySQL | 8.0+ | |

> **PHP version gotcha.** If Laragon's active PHP is still 8.2, `php artisan` will
> refuse to boot. Either switch Laragon's version, or call the 8.4 binary directly:
> `D:\laragon\bin\php\php-8.4.6-Win32-vs17-x64\php.exe artisan ...`

## Setup

```bash
# Backend
cd backend
cp .env.example .env          # then set DB_DATABASE / DB_USERNAME / DB_PASSWORD
php artisan key:generate
php artisan migrate --seed
php artisan serve             # http://localhost:8000

# Frontend
cd frontend
npm install
npm run dev                   # http://localhost:3000
```

Both must run at once — the storefront reads everything from the API.

### npm note

`frontend/.npmrc` pins `legacy-peer-deps=true`. npm 10.9.4 (bundled with Node 22)
crashes resolving Nuxt's peer graph with `Cannot read properties of null (reading
'edgesOut')`. Remove the setting once npm is upgraded past that bug.

## Architecture notes

**Auth** is Sanctum in SPA (cookie) mode, not bearer tokens — nothing sensitive
touches `localStorage`. This requires the API and storefront to share a
registrable domain: `localhost` in development (cookies ignore the port),
`atelier.test` + `api.atelier.test` in production.

**Money** is stored as integer minor units (poisha) throughout. Never floats —
totals must not drift.

**Payments** sit behind the `App\Services\Payment\PaymentGateway` contract, so
adding bKash alongside SSLCommerz is one class plus a `config/payment.php` entry.
`PAYMENT_DRIVER=cod` needs no credentials and is the local default.

## Configuration

| File | Purpose |
|---|---|
| `backend/config/shop.php` | Brand name, currency, cart and catalog rules |
| `backend/config/payment.php` | Gateway credentials and redirect targets |
| `backend/config/cors.php` | Allowed storefront origins (explicit — no wildcard, credentials are on) |
| `frontend/nuxt.config.ts` | API base URL, modules, Tailwind |
| `frontend/app/assets/css/main.css` | Design tokens |
