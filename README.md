# Moneymosaic

A modern personal finance tracker that helps you keep a clear, organized picture of your income and spending. Built as a single-page application on top of Laravel 12, Inertia.js v2, React 19, and Tailwind CSS v4.

## Overview

Moneymosaic is designed for individuals who want more than a simple spreadsheet to track their money. It combines a fast, SPA-like user experience with a classic server-driven architecture, so every page load feels instant while all business logic, validation, and authorization stay safely on the backend.

Typical use cases include:

- Logging day-to-day income and expenses with rich metadata (categories, tags, notes).
- Grouping entries by customizable income and expense **types** to match your personal accounting style.
- Reviewing paginated, filterable lists of transactions with persistent user preferences.
- Enforcing per-user access rules so that each account only sees and modifies its own data.

## Tech Stack

- **Backend:** PHP 8.4, Laravel 12
- **Frontend:** React 19, Inertia.js v2, Tailwind CSS v4, Vite
- **Routing bridge:** Ziggy (named Laravel routes available in React)
- **Database:** SQLite by default; works with any Laravel-supported driver (MySQL, PostgreSQL, etc.)
- **Testing:** PHPUnit 11 with feature and unit tests
- **Tooling:** Laravel Boost, Pint (code style), Pail (log tailing), Sail (Docker), ESLint, Prettier
- **Extras:** `spatie/laravel-tags` for tagging incomes and expenses

## Features

- **Incomes & Expenses** — create, edit, and delete transactions with amount, date, description, category, and tags.
- **Types** — organize transactions by user-defined income/expense types with full CRUD support.
- **Tagging** — attach multiple tags to any entry for flexible grouping and filtering.
- **Authorization** — Laravel policies ensure that users can only access and mutate resources they own.
- **Pagination preferences** — per-user pagination settings are remembered between sessions.
- **Toast feedback** — inline success/error toasts on create and update flows.
- **SPA-like UX** — powered by Inertia.js, with deferred props, prefetching, and optimistic navigation.
- **Fully tested** — an extensive PHPUnit suite covering happy paths, failure paths, and edge cases.

## Requirements

- PHP **>= 8.4** with the usual Laravel extensions (mbstring, openssl, pdo, sqlite, tokenizer, xml, ctype, json, bcmath)
- Composer **2.x**
- Node.js **>= 20** and npm
- SQLite (default) or any other Laravel-supported database

## Installation

```bash
# 1. Clone the repository
git clone https://github.com/uros196/moneymosaic.git
cd moneymosaic

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Prepare the environment file and app key
cp .env.example .env
php artisan key:generate

# 5. Create the SQLite database file (only if using SQLite)
#    On Linux/macOS:
touch database/database.sqlite
#    On Windows (PowerShell):
#    New-Item -ItemType File -Path database\database.sqlite

# 6. Run migrations (and seeders, if any are defined)
php artisan migrate
```

After these steps, the application is ready to run locally.

## Development

The simplest way to start everything (PHP server, queue listener, and the Vite dev server) is:

```bash
composer run dev
```

This uses `concurrently` to run the three processes in parallel with colored, labeled output.

If you prefer to control each process separately, run them in three terminals:

```bash
# Terminal 1 — HTTP server
php artisan serve

# Terminal 2 — Queue worker (for jobs, notifications, etc.)
php artisan queue:listen --tries=1

# Terminal 3 — Vite dev server (hot module reload for React/Tailwind)
npm run dev
```

### Useful commands

```bash
# Tail application logs in real time
php artisan pail

# List all registered routes
php artisan route:list

# Inspect a specific config value
php artisan config:show app.name

# Format PHP code according to project style
vendor/bin/pint

# Lint and format frontend code
npm run lint
npm run format
```

## Building for Production

Compile and minify frontend assets:

```bash
npm run build
```

Then follow your preferred Laravel deployment workflow (for example, [Laravel Cloud](https://cloud.laravel.com/), Forge, or a custom server setup). Remember to:

- Set `APP_ENV=production` and `APP_DEBUG=false` in your production `.env`.
- Cache configuration and routes (`php artisan config:cache`, `php artisan route:cache`).
- Run migrations on the target environment (`php artisan migrate --force`).

## Testing

The project ships with a comprehensive PHPUnit test suite.

```bash
# Run the full suite with compact output
php artisan test --compact

# Run a single test file
php artisan test --compact tests/Feature/Incomes/IncomeTest.php

# Filter by test name
php artisan test --compact --filter=IncomeTagsTest
```

Feature tests live in `tests/Feature/` and cover controllers, policies, validation, and Inertia responses. Unit tests live in `tests/Unit/`.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
