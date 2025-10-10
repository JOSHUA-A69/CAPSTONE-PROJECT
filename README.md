# eReligiousServices — Project Overview & Setup

This repository contains the eReligiousServices web application built with Laravel (PHP) and bundled with Docker Compose for local development. This README documents how to set up the project on another machine, the system overview, and troubleshooting tips.

## Table of contents
- Project overview
- Requirements
- Quick start (recommended)
- Manual setup (without Docker)
- Database migrations & seeding
- Environment variables
- Common tasks (caching, composer, artisan)
- Testing and verifying email / remember-me
- Admin: user management and role-based routing
- Troubleshooting
- Contributing

---

## Project overview

eReligiousServices is a Laravel-based web application that implements role-based authentication and routing. Main features:

- Authentication (register / login), including role selection: `admin`, `staff`, `adviser`, `requestor`, `priest`.
- Registration maps role names to a normalized `user_roles` table and stores `user_role_id` on `users` for compatibility.
- Role-based dashboards and middleware protect role areas (`/admin`, `/staff`, etc.).
- Profile editing that matches the `users` table (`first_name`, `middle_name`, `last_name`, `email`, `phone`).
- Admin user management (admin can delete other users via a protected route).

The project ships with Docker Compose configuration to run PHP, MySQL, and phpMyAdmin locally.

## Requirements

- Git
- Docker & Docker Compose
- On host: PowerShell (Windows) or a POSIX shell on macOS / Linux. The docs below include PowerShell examples.

If you prefer not to use Docker, you need PHP 8.x, Composer, MySQL 8 (or compatible), Node.js for assets, and the usual Laravel extensions (mbstring, pdo, bcmath, openssl, etc.). See `composer.json` for dependencies.

## Quick start (Docker Compose — recommended)

1. Clone the repo:

```powershell
git clone <repo-url> eReligiousServices
cd eReligiousServices
```

2. Copy the environment file and set a secure APP_KEY (or generate it later):

```powershell
cp .env.example .env
# Open .env and set DB credentials if needed; the included docker-compose uses a local MySQL service.
```

3. Build and start services (in background):

```powershell
docker-compose up -d --build
```

4. Install Composer dependencies inside the app container and generate an app key:

```powershell
docker-compose exec app composer install --no-interaction --prefer-dist
docker-compose exec app php artisan key:generate
```

5. Run migrations and seeders:

```powershell
docker-compose exec app php artisan migrate --seed
```

6. (Optional) If you're using the included Mailhog or SMTP dev mailbox, check the container logs or use `php artisan route:list` and `tinker` to find verification links during development.

7. Open the app in your browser (default from compose): http://localhost:8000

Notes:
- If containers fail due to ports or missing images, inspect `docker-compose.yml` and `docker-compose logs`.
- If you change route/middleware/controller code, clear caches inside the container:

```powershell
docker-compose exec app php artisan config:clear; docker-compose exec app php artisan route:clear; docker-compose exec app php artisan view:clear
```

---

## Manual setup (no Docker)

1. Install PHP 8.x, Composer, MySQL, and Node.js.
2. Clone repository and run `composer install`.
3. Copy `.env.example` to `.env` and set DB credentials.
4. Run `php artisan key:generate`.
5. Run migrations `php artisan migrate --seed`.
6. Serve with `php artisan serve` or a proper webserver.

## Database migrations & seeding

- Migrations are under `database/migrations` and include schema adjustments (split name fields, user_roles table, mapping to users.user_role_id).
- There are idempotent seeders to create the `user_roles` rows and some test users used for development (see `database/seeders`).

If you made changes to migration files during development, inspect the migrations directory. To reset and re-run migrations (dev-only):

```powershell
docker-compose exec app php artisan migrate:fresh --seed
```

Be careful when running `migrate:fresh` — it drops all data.

## Environment variables

Important `.env` keys to check:

- APP_ENV, APP_DEBUG, APP_URL
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- MAIL_MAILER and mail config for dev email testing

When using Docker Compose the DB host is typically set to the service name (e.g., `db` or `mysql_db`) or `127.0.0.1` when using direct host networking. Check `docker-compose.yml` and `.env` in this repo.

## Common artisan & composer tasks

- Clear caches: `php artisan config:clear && php artisan route:clear && php artisan view:clear`
- Rebuild composer autoload: `composer dump-autoload -o`
- Run tests (if added): `vendor/bin/phpunit`

When using Docker Compose prefix those with `docker-compose exec app`.

## Testing email verification and remember-me

- `email_verified_at` is NULL until a user clicks a signed verification link. Registration triggers an event which (if mail is configured) sends the link.
- `remember_token` is populated when users sign in with the "Remember me" option; this persists login across browser sessions.

Manual checks:

```powershell
# Check a user's email_verified_at
docker-compose exec app php artisan tinker --execute "\App\Models\User::where('email','user@example.com')->first()->email_verified_at"

# Check remember_token after login (use a test account or seeders)
docker-compose exec app php artisan tinker --execute "\App\Models\User::where('email','user@example.com')->first()->remember_token"
```

## Admin: user management & role-based routing

- Role-based dashboards are available at `/admin`, `/staff`, `/adviser`, `/priest`, and `/requestor` and protected by a `RoleMiddleware` in `app/Http/Middleware/RoleMiddleware.php`.
- Admins can delete other users via a controller `/admin/users/{id}` DELETE route named `admin.users.destroy` protected by the `role:admin` middleware.

## Troubleshooting

- Target class [role] does not exist: If you see `Target class [role] does not exist.` in logs, clear caches and ensure middleware registration is in `app/Http/Kernel.php`:

```php
'role' => \App\Http\Middleware\RoleMiddleware::class,
```

- Database connection errors: Verify `.env` DB_HOST and that the DB container is healthy. Use `docker-compose logs db` to inspect.
- Duplicate migration errors: If migrations were modified repeatedly and a column already exists, either roll back the migration that added it or use `migrate:fresh` for dev environments.

## Contributing

If you'd like to contribute, please:

1. Create an issue describing the change.
2. Create a feature branch.
3. Run tests and ensure lints pass where applicable.

# eReligiousServices — Project overview and setup

This repository contains eReligiousServices — a Laravel application for booking and community engagement used by the Center for Religious Education and Mission (CREaM). This README provides a concise, practical setup guide for other machines (both Docker-based and manual), a refined overview of the project, and troubleshooting tips for common problems when cloning and booting the project.

## Table of contents
- Overview
- Requirements
- Quick start (Docker — recommended)
- Manual setup (host machine without Docker)
- Building frontend assets (Vite/Tailwind)
- Database migrations & seeding
- Environment and missing files after clone
- Useful composer / artisan / docker commands
- Ports and services (what runs where)
- Troubleshooting
- Contributing

---

## Overview

eReligiousServices is a Laravel (PHP) web application focused on event/liturgy booking and community workflows. Key capabilities:

- Role-based authentication and dashboards (roles: admin, staff, adviser, requestor, priest).
- Registration and email verification flows.
- Admin user management and role assignment.
- Online booking, event listings, and basic profile management (first/middle/last names, phone, email).
- Vite + Tailwind for frontend assets; MySQL for data storage; Mailhog included for local email testing.

The repository includes a Docker Compose setup to make running locally easier and consistent across machines.

## Requirements

- Git
- Docker Engine + Docker Compose (recommended) — modern Docker (Docker Desktop) that supports `docker compose` CLI
- If not using Docker: PHP 8.1+ (with pdo_mysql, mbstring, bcmath, xml, gd), Composer, MySQL 8+, Node.js (18+ recommended), npm/yarn

Notes for Windows users: these docs use PowerShell examples; on macOS / Linux run the equivalent shell commands.

## Quick start (Docker — recommended)

This is the fastest and most reproducible way to get the project running on another machine.

1. Clone the repo and cd into it:

```powershell
git clone <repo-url> eReligiousServices
cd eReligiousServices
```

2. Create a `.env` file (copy the example). If your clone does not include `.env` create it now:

```powershell
copy .env.example .env
# open .env and set APP_NAME, APP_URL if you like; default DB values below match docker-compose.yml
```

3. Start containers (build and run in background):

```powershell
docker compose up -d --build
```

4. Install PHP dependencies inside the `app` container and generate an app key:

```powershell
docker compose exec app composer install --no-interaction --prefer-dist
docker compose exec app php artisan key:generate
```

5. Run database migrations and seeders:

```powershell
docker compose exec app php artisan migrate --seed
```

6. Install and build frontend assets (inside container):

```powershell
docker compose exec app sh -c "npm ci --silent && npm run build --silent"
```

7. Open the app in your browser:

- Application: http://localhost:8000
- MailHog (dev SMTP UI): http://localhost:8025 (if Mailhog service is running)
- phpMyAdmin: http://localhost:8080 (if enabled in compose)

8. Helpful commands (stop, restart, logs):

```powershell
# stop and remove containers and network
docker compose down --remove-orphans

# stop only
docker compose stop

# show logs for a service
docker compose logs -f app

# show running compose services
docker compose ps
```

## Manual setup (no Docker)

If you prefer not to use Docker, set up on the host

1. Install PHP 8.1+ with required extensions (pdo_mysql, mbstring, bcmath, xml, gd, etc.)
2. Install Composer and Node.js (18+)
3. Clone repo and copy `.env.example` to `.env`
4. Install PHP deps:

```powershell
composer install --no-interaction --prefer-dist
```

5. Install Node deps and build assets:

```powershell
npm ci
npm run build
```

6. Generate an app key and run migrations:

```powershell
php artisan key:generate
php artisan migrate --seed
```

7. Serve app locally:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

## Building frontend assets (Vite + Tailwind)

Assets are located under `resources/js` and `resources/css`. The project uses Vite. Build options:

- Inside the app container (recommended when using Docker):

```powershell
docker compose exec app sh -c "npm ci && npm run build"
```

- On the host (non-Docker):

```powershell
npm ci
npm run build
```

The production output will be placed in `public/build` and referenced by Blade when `public/build/manifest.json` exists.

## Database migrations & seeding

- Run migrations:

```powershell
docker compose exec app php artisan migrate
```

- Migrate fresh + seed (development only — drops data):

```powershell
docker compose exec app php artisan migrate:fresh --seed
```

## Environment & missing files after clone

When cloning from a remote repository the following are commonly missing or need to be created:

- `.env` — copy `.env.example` and set values (APP_KEY, DB credentials, MAIL settings).
- `vendor/` — created by running `composer install`.
- `node_modules/` — created by running `npm ci` or `npm install`.
- `storage/` and `bootstrap/cache` — when missing, create them and (on Linux/macOS) set permissions so the webserver/process can write:

```powershell
# create folders if missing
mkdir storage\framework storage\logs bootstrap\cache

# on Linux/macOS example permissions (adjust user/group as needed)
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R ug+rwx storage bootstrap/cache
```

Windows note: permissions are usually okay for local development, but ensure your editor/antivirus is not locking files.

## Useful composer / artisan / docker commands

- Composer install: `composer install --no-interaction --prefer-dist`
- Composer dump autoload: `composer dump-autoload -o`
- Clear caches: `php artisan config:clear && php artisan route:clear && php artisan view:clear`
- Generate key: `php artisan key:generate`
- Run tests: `vendor/bin/phpunit` (or `./vendor/bin/phpunit` on POSIX)

When using Docker prefix with `docker compose exec app` (for example `docker compose exec app php artisan migrate`).

## Ports & services (defaults used by compose)

- App (PHP built-in server inside container): http://localhost:8000
- MailHog web UI (dev SMTP): http://localhost:8025 (SMTP on port 1025)
- phpMyAdmin: http://localhost:8080 (if enabled in compose)

Check `docker-compose.yml` for exact ports and service names (service name is `app` in this project; container_name may be `laravel_app`).

## Troubleshooting

- If the app title remains "Laravel":
	- Edit `.env` and set `APP_NAME="eReligiousServices"` and clear config cache. If you changed `config/app.php` fallback, clear config cache too.

- Composer memory errors on install:
	- Use `COMPOSER_MEMORY_LIMIT=-1 composer install` or allocate more memory to Docker if running in a container.

- Node build errors:
	- Ensure Node 18+ is installed. On containers run `docker compose exec app node -v` to inspect.

- Container exec reporting not running: be sure to use the Compose service name. For this repo the service is `app`:

```powershell
# correct
docker compose exec app sh -c "npm ci && npm run build"

# incorrect (container name instead of service may fail with some docker compose versions)
docker compose exec laravel_app sh -c "..."
```

- Database connection errors: confirm `.env` DB_HOST matches the compose service name (commonly `db` or `mysql_db`), or when running on host use `127.0.0.1` and the host port mapping.

- If Blade templates or config changes don't show up, clear caches:

```powershell
docker compose exec app php artisan config:clear
docker compose exec app php artisan view:clear
docker compose exec app php artisan route:clear
```

## Contributing

If you'd like to contribute:

1. Fork and create a feature branch.
2. Run tests and keep changes small and documented.
3. Open a PR describing the change and its reasoning.

