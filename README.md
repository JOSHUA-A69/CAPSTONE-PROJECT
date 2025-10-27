# eReligiousServices — Project Overview & Setup

This repository contains the eReligiousServices web application built with Laravel (PHP) and bundled with Docker Compose for local development. This README documents how to set up the project on another machine, the system overview, and troubleshooting tips.

## Table of contents
- Project overview
- Requirements
- Quick start (recommended)
- Manual setup (without Docker)
- Database migrations & seeding

# eReligiousServices — Quick setup

Short, practical instructions to get this Laravel app running on another machine.

## Quick start (Docker — recommended)

1. Clone and create env:

git clone https://github.com/JOSHUA-A69/CAPSTONE-PROJECT 
# For Docker + MySQL:
copy .env.docker.example .env
# Or copy default and edit DB settings manually (see below)
# copy .env.example .env

2. Start containers and install deps:

docker compose up -d --build
docker compose exec app composer install --no-interaction --prefer-dist
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app sh -c "npm ci --silent && npm run build --silent"
docker compose exec app php artisan storage:link

3. Open:

- App: http://localhost:8000
- MailHog: http://localhost:8025
- phpMyAdmin: http://localhost:8080

4. If migrations fail with a SQLite error, ensure your .env is configured for MySQL when using Docker:

```env
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=ereligious_db
DB_USERNAME=laravel
DB_PASSWORD=laravel
```

Then rerun:

```powershell
docker compose exec app php artisan migrate --seed
```

## Manual (no Docker) — minimal

```powershell
copy .env.example .env
composer install
php artisan key:generate
npm ci
npm run build
php artisan migrate --seed
php artisan storage:link
php artisan serve --host=127.0.0.1 --port=8000
```

## Dependencies (high level)

- PHP: Laravel ^12 (requires PHP 8.2+)
- Composer packages: laravel/framework, laravel/tinker, (dev: breeze, pint, phpunit, etc.)
- JS tooling: Vite, Tailwind, Alpine, axios

Restore exact versions with `composer install` (uses composer.lock) and `npm ci` (uses package-lock.json).

## Docker services

- app (container_name: `laravel_app`) — PHP app (port 8000)
- db (`mysql_db`) — MySQL 8 (port 3306)
- phpmyadmin (`phpmyadmin`) — optional DB UI (port 8080)
- mailhog (`mailhog`) — dev SMTP (ports 1025/8025)

## Quick troubleshooting

- Ports busy: change `docker-compose.yml` ports or stop the conflicting service.
- Composer OOM: `COMPOSER_MEMORY_LIMIT=-1 composer install`.
- Nothing shows after changes: `php artisan config:clear && php artisan view:clear`.

---

For any extra documentation (PowerShell scripts, expanded dependency file, or docs folder) tell me which one and I'll add it.
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

## Dependencies

The project uses PHP packages (managed by Composer) and JavaScript packages (managed by npm). Restore them with `composer install` and `npm ci` respectively.

PHP (composer) highlights (see `composer.json`):
- php ^8.2
- laravel/framework ^12.0
- laravel/tinker ^2.10.1

Dev-only (composer require-dev): fakerphp/faker, laravel/breeze, laravel/pint, laravel/sail, mockery/mockery, nunomaduro/collision, phpunit/phpunit, etc.

JavaScript (npm) highlights (see `package.json` devDependencies):
- tailwindcss, @tailwindcss/forms, laravel-vite-plugin, vite, autoprefixer, postcss, alpinejs, axios

Use `composer.lock` and `package-lock.json` (if present) to restore exact versions. `composer install` and `npm ci` will respect those lockfiles.

## Docker services (what runs locally)

- app (container_name: `laravel_app`) — PHP application using the PHP built-in server. Port: 8000
- db (container_name: `mysql_db`) — MySQL 8.0. Port: 3306
- phpmyadmin (container_name: `phpmyadmin`) — optional database UI. Port: 8080
- mailhog (container_name: `mailhog`) — dev SMTP + web UI. SMTP port: 1025, Web UI: 8025

These services are defined in `docker-compose.yml` and the named volume `db_data` persists MySQL data.

## One-line restore (Docker)

Copy/paste to get a fresh machine up and running (PowerShell):

```powershell
git clone <repo-url> eReligiousServices; cd eReligiousServices
copy .env.example .env
docker compose up -d --build
docker compose exec app composer install --no-interaction --prefer-dist
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app sh -c "npm ci --silent && npm run build --silent"
docker compose exec app php artisan storage:link
```

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

