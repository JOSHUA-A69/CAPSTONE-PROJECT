# Deployment Guide for Render

This project is configured for deployment on [Render](https://render.com) using Docker and a PostgreSQL database.

## Prerequisites
1. A Render account.
2. This repository connected to your Render account.

## Deployment Steps (Blueprint)

We have included a `render.yaml` Blueprint file which automates the setup.

1. Go to your Render Dashboard.
2. Click **New +** and select **Blueprint**.
3. Connect this repository.
4. Render will detect `render.yaml` and propose creating:
   - **eReligiousServices** (Web Service)
   - **mysql** (Private Service - Docker MySQL 8.0)
5. Click **Apply**.

**Note on MySQL:**
We are using a "Private Service" with a persistent Disk for MySQL, as Render does not offer managed MySQL. **This typically requires a paid Render plan** to support the persistent disk.

## Configuration

The Blueprint links these services automatically using the service name `mysql`.

1. **App Key**:
   Create the environment variable `APP_KEY` in the **eReligiousServices** service settings (Generate one locally with `php artisan key:generate --show`).

2. **Database Password**:
   The `render.yaml` uses a default placeholder password (`change_this_password_in_dashboard`).
   **For Security:**
   - Go to your **mysql** service -> Environment. Update `MYSQL_PASSWORD`.
   - Go to your **eReligiousServices** service -> Environment. Update `DB_PASSWORD` to match.
   - Triger a manual deploy if needed.

## Manual Deployment (Docker)

If you prefer not to use Blueprints:

1. Create a **New Web Service**.
2. Source: **Git** (Connect repo).
3. Runtime: **Docker**.
4. **Build Context**: `.` (root directory).
5. **Dockerfile Path**: `docker/prod/Dockerfile`.
6. Add Environment Variables:
   - `APP_ENV`: `production`
   - `APP_KEY`: (your generated key)
   - `DB_CONNECTION`: `pgsql` (recommended) or `mysql`
   - `DB_HOST`, `DB_DATABASE`, `DB_USER`, `DB_PASSWORD` (from your database provider).

## Post-Deployment

After the first successful deployment, the `entrypoint.sh` script handles:
- Storage linking (`php artisan storage:link`)
- Config caching

**Database Migrations:**
You should run migrations manually via the Shell tab in Render or add a Job, to avoid accidental data loss during automatic deploys.
1. Open the service in Render.
2. Go to the **Shell** tab.
3. Run: `php artisan migrate --force`

## Troubleshooting

- **500 Error**: Check `Log` tab. usually missing `APP_KEY` or DB connection issue.
- **Styling Missing**: Ensure `npm run build` ran (steps are in Dockerfile) and `ASSET_URL` is correct or `APP_URL` matches your browser URL.
