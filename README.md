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

---

If you want, I can also:

- Add a dedicated `docs/` folder with more granular developer guides (e.g., database design, ER diagrams).
- Scaffold an Admin users list UI (table + delete actions) so admins can manage users via the web UI.
- Add sample `.env.testing` and a `Makefile` or PowerShell script for faster dev bootstrapping.

Tell me which extras you want and I will add them next.
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[WebReinvent](https://webreinvent.com/)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[Cyber-Duck](https://cyber-duck.co.uk)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Jump24](https://jump24.co.uk)**
- **[Redberry](https://redberry.international/laravel/)**
- **[Active Logic](https://activelogic.com)**
- **[byte5](https://byte5.de)**
- **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
