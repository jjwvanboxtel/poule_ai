# Quickstart: Voetbalpoule & Voorspelsysteem

## Purpose

This quickstart describes the intended local-development and shared-hosting deployment flow for the planned PHP application. It is a design target for implementation, not a claim that all commands already exist.

## Prerequisites

- PHP 8.4.x with PDO MySQL extension enabled
- MySQL 8.x
- Composer 2.x
- Node.js 20+ and npm (for Playwright tooling)
- Apache-compatible environment for production/shared hosting

## Local Setup

### 1. Install dependencies

```bash
composer install
npm install
```

### 2. Create environment configuration

Create `.env` from `.env.example` and configure:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8080

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=voetbalpoule
DB_USER=root
DB_PASS=secret

SESSION_COOKIE=voetbalpoule_session
CSRF_TOKEN_NAME=_token
UPLOAD_PATH=storage/uploads/logos
```

### 3. Create the database

```sql
CREATE DATABASE voetbalpoule CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 4. Run schema and seeders

```bash
php bin/migrate.php
php bin/seed.php
```

### 5. Start the local server

```bash
php -S 127.0.0.1:8080 -t public
```

### 6. Run automated checks

```bash
vendor/bin/phpstan analyse
vendor/bin/phpunit
npx playwright test
```

## First Manual Validation Flow

1. Register a participant account.
2. Sign in as an administrator.
3. Create a competition with valid prize percentages totaling 100.
4. Activate at least one prediction section.
5. Import or create entities, groups, venues, and matches.
6. Open the competition for submissions.
7. Submit one complete participant prediction before the deadline.
8. Enter match results as admin.
9. Verify that standings snapshots are created and public standings update.

## Shared Hosting Deployment Notes

- Point the web root to `public/`.
- Ensure Apache rewrite rules forward all non-static requests to `public/index.php`.
- Confirm writable permissions for:
  - `storage/cache`
  - `storage/logs`
  - `storage/uploads/logos`
- Run migrations through the approved deployment path for the host:
  - SSH/CLI if available, or
  - a protected admin maintenance workflow if CLI is unavailable
- Never expose writable upload directories as executable script locations.

## Operational Guardrails

- Do not rely on workers, queues, or long-running daemons.
- Treat standings recalculation as synchronous admin-triggered or result-triggered application work.
- Enforce deadline checks and role checks server-side even when UI controls hide the action.
