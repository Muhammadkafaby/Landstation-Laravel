# Land Station

Web-based management system untuk bisnis Land Station:

- Cafe
- Billiard
- PlayStation
- Rental RC

Stack utama:

- Laravel 12
- Inertia React
- Vite 7
- SQLite/MySQL-compatible schema via Laravel migrations

## Modul yang sudah ada

- Public website data-backed
- Booking create + lifecycle management
- Management CRUD untuk service, unit, pricing rule, booking policy
- POS session control
- POS cafe order flow
- Invoice builder + payment verification
- POS checkout
- Reports, customer history, transaction ledger
- Search, filter, CSV export
- Audit trail + audit log viewer

## Requirement lokal

- PHP 8.2+
- Composer
- Node.js `^20.19.0 || >=22.12.0`
- npm

Catatan:

- Vite 7 tidak kompatibel dengan Node `20.10.0`
- File `.nvmrc` disediakan untuk menyamakan versi lokal

## Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
```

Atau gunakan helper Composer:

```bash
composer setup
```

## Menjalankan aplikasi lokal

```bash
composer dev
```

Ini akan menjalankan:

- Laravel dev server
- queue listener
- log tail
- Vite dev server

## Verifikasi utama

Backend tests:

```bash
php artisan test
```

Frontend production build:

```bash
npm run build
```

Reset database lokal:

```bash
php artisan migrate:fresh --seed
```

## Produksi / hardening

Checklist minimum:

- gunakan Node yang sesuai dengan `package.json` engines / `.nvmrc`
- jalankan `php artisan test`
- jalankan `npm run build`
- jalankan `php artisan migrate --force`
- verifikasi export/reporting dan flow pembayaran manual
- review `audit_logs` untuk jejak aksi operator kritikal

## CI

Workflow GitHub Actions sekarang memverifikasi:

- install dependency PHP
- install dependency npm
- build frontend dengan Node yang sesuai
- jalankan test suite Laravel
