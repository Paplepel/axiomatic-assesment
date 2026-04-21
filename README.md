# AXIOMATIC Technical Assessment — Commission Payment Notes

A commission payment note management application built with **Laravel 13**, **Inertia.js**, **Vue 3 + TypeScript**, **Pinia**, **Spatie Laravel Permission**, and **Pest**.

---

## Stack

| Layer       | Technology                                      |
|-------------|-------------------------------------------------|
| Backend     | Laravel 13.x, PHP 8.4-FPM                      |
| Frontend    | Vue 3, TypeScript, Vite, Pinia                 |
| Bridge      | Inertia.js                                      |
| Database    | MariaDB 11                                      |
| Permissions | Spatie Laravel Permission                       |
| Tests       | Pest 4 (Laravel plugin)                         |
| Server      | Nginx 1.25                                      |
| Container   | Docker / Docker Compose                         |

---

## Prerequisites

- Docker >= 24
- Docker Compose >= 2.20
- Node.js >= 20 (only needed to rebuild frontend assets locally; pre-built assets are included)

---

## Setup

### 1. Clone and configure

```bash
git clone <repo-url> axiomatic-assessment
cd axiomatic-assessment
cp .env.example .env      # already committed; skip if .env is present
```

### 2. Start the Docker stack

```bash
docker compose up -d
```

All four services start automatically: `app` (PHP-FPM), `webserver` (Nginx), `db` (MariaDB 11), `node` (Vite build). The `db` container uses a health-check; the `app` container waits for it before accepting connections.

### 3. Install PHP dependencies

```bash
docker compose exec app composer install
```

### 4. Generate application key

```bash
docker compose exec app php artisan key:generate
```

### 5. Run migrations

```bash
docker compose exec app php artisan migrate
```

### 6. Seed demo data

```bash
docker compose exec app php artisan db:seed
```

This creates:

| Role    | Email                   | Password   |
|---------|-------------------------|------------|
| Manager | admin@example.com       | `password` |
| Viewer  | viewer@example.com      | `password` |

It also seeds one company (**Spar Group**) with two branches (Cape Town, Johannesburg) and two commission notes:
- Alice Botha — R10 000
- Bob Smith — R20 000

### 7. (Optional) Rebuild frontend assets

If you change TypeScript/Vue files:

```bash
docker compose exec node npm run build
```

---

## Access

| Resource               | URL / Address                                    |
|------------------------|--------------------------------------------------|
| Web application        | http://localhost:8080                            |
| Commission notes page  | http://localhost:8080/commission-notes           |
| MariaDB (external)     | `localhost:3307` — user: `axiomatic`, pass: `secret`, db: `axiomatic` |

---

## Running Tests

```bash
docker compose exec app vendor/bin/pest
```

Expected output: **33 tests, 73 assertions — all passing.**

The commission note tests are in `tests/Feature/CommissionNoteTest.php` and cover:
- Unauthenticated redirect (guest)
- Viewer read-only access
- Viewer cannot create or update
- Manager can create notes
- Author can edit their own note
- Non-author viewer cannot edit another user's note
- Manager can edit any note

---

## Project Structure

```
app/
├── Http/
│   ├── Controllers/CommissionNoteController.php   # index, store, update, destroy
│   └── Requests/
│       ├── StoreCommissionNoteRequest.php
│       └── UpdateCommissionNoteRequest.php
├── Models/
│   ├── CommissionNote.php
│   ├── Company.php
│   ├── Branch.php
│   └── Employee.php
├── Policies/CommissionNotePolicy.php              # viewAny, create, update, delete
└── Services/CommissionNoteService.php             # business logic layer

resources/js/
├── Pages/CommissionNotes/Index.vue                # CRUD page (Vue 3 + Inertia)
├── stores/commissionNoteStore.ts                  # Pinia store
└── types/models.ts                                # TypeScript interfaces

database/
├── migrations/                                    # 8 migrations
├── factories/                                     # 4 factories
└── seeders/DatabaseSeeder.php

tests/
├── bootstrap.php                                  # sets APP_ENV=testing before Laravel boots
└── Feature/
    ├── CommissionNoteTest.php                     # 8 feature tests
    └── Auth/                                      # Breeze auth tests (25 tests)
```

---

## Ports

| Service   | Internal | External |
|-----------|----------|----------|
| Nginx     | 80       | 8080     |
| MariaDB   | 3306     | 3307     |
| PHP-FPM   | 9000     | —        |
