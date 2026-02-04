# Deployment Guide for Render

This project is configured for deployment on [Render](https://render.com) using Docker and a PostgreSQL database.

## Prerequisites
1. A Render account.
2. This repository connected to your Render account.

## Deployment Steps (Blueprint with TiDB Cloud)

1. **Setup Database first (TiDB Cloud)**
   - Go to [TiDB Cloud](https://tidbcloud.com/) and sign up (Free).
   - Create a new **Serverless Tier** cluster.
   - Once created, click **Connect**.
   - Select **PHP / PDO** or just copy the parameters:
     - **Host**: (e.g., `gateway01.us-west-2.prod.aws.tidbcloud.com`)
     - **Port**: `4000`
     - **User**: (e.g., `2SeE...prefix.root`)
     - **Password**: (The one you generated)
     - **Database**: `test` (or create a new one named `ers_db`)

2. **Deploy to Render**
   - Go to Render Dashboard -> **New +** -> **Blueprint**.
   - Connect this repository.
   - Render will detect `render.yaml`.
   - It will ask for the environment variables defined in the file (`DB_HOST`, `DB_USERNAME`, etc.).
   - Paste the values from TiDB Cloud.
     - `DB_PORT`: `4000`
     - `MYSQL_ATTR_SSL_CA`: `/etc/ssl/certs/ca-certificates.crt` (Pre-filled)
   - Click **Apply**.

3. **Post-Deployment**
   - Render will build the app.
   - Once "Live", go to the **Shell** tab in Render.
   - Run migrations: `php artisan migrate --force`
   - Run seeders (optional): `php artisan db:seed --force`

## Manual Configuration Notes

If you are not using the Blueprint:
ensure you set `MYSQL_ATTR_SSL_CA` to `/etc/ssl/certs/ca-certificates.crt`.
TiDB requires an SSL connection, and the standard Linux CA bundle covers this.

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
