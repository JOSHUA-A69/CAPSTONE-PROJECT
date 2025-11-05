# Setup Guide - Religious Services Reservation System

Complete guide to set up this Laravel project on a new device from scratch.

---

## 📋 Prerequisites

### Required Software

1. **PHP 8.2 or higher**

    - Download: https://windows.php.net/download/
    - Choose: PHP 8.2+ Thread Safe ZIP
    - Extract to: `C:\php`
    - Add to System PATH: `C:\php`

2. **Composer** (PHP Dependency Manager)

    - Download: https://getcomposer.org/download/
    - Run installer (it will detect PHP)

3. **Node.js & NPM** (v18 or higher)

    - Download: https://nodejs.org/
    - Choose LTS version
    - Verify: `node -v` and `npm -v`

4. **MySQL or MariaDB** (Database)

    - Option A - XAMPP: https://www.apachefriends.org/
    - Option B - MySQL: https://dev.mysql.com/downloads/installer/
    - Option C - Laragon: https://laragon.org/ (Recommended - includes PHP, MySQL, Apache)

5. **Git** (Version Control)

    - Download: https://git-scm.com/download/win
    - Install with default options

6. **Code Editor** (Choose one)
    - Visual Studio Code: https://code.visualstudio.com/
    - Sublime Text: https://www.sublimetext.com/
    - PhpStorm: https://www.jetbrains.com/phpstorm/
    - Or any text editor you prefer

---

## 🚀 Installation Steps

### Step 1: Clone the Repository

```bash
# Navigate to your desired directory
cd C:\Users\[YourName]\Desktop

# Clone the repository
git clone https://github.com/jamesa4a1/CAPSTONE-PROJECT.git

# Enter the project directory
cd CAPSTONE-PROJECT
```

### Step 2: Install PHP Dependencies

```bash
# Install all PHP packages using Composer
composer install
```

If you get memory limit errors:

```bash
php -d memory_limit=-1 C:\ProgramData\ComposerSetup\bin\composer.phar install
```

### Step 3: Install Node Dependencies

```bash
# Install all JavaScript/CSS packages
npm install
```

### Step 4: Configure Environment

```bash
# Copy the example environment file
copy .env.example .env
```

Or manually create `.env` file and copy contents from `.env.example`

### Step 5: Edit .env File

Open `.env` file and configure:

```env
APP_NAME="Religious Services"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=religious_services
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration (for testing, use Mailtrap or log)
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@religiousservices.local"
MAIL_FROM_NAME="${APP_NAME}"

# Queue Configuration
QUEUE_CONNECTION=database

# Session Configuration
SESSION_DRIVER=database
SESSION_LIFETIME=120
```

### Step 6: Generate Application Key

```bash
php artisan key:generate
```

### Step 7: Create Database

**Option A - Using XAMPP/Laragon:**

1. Start MySQL from control panel
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create new database: `religious_services`
4. Set Collation: `utf8mb4_unicode_ci`

**Option B - Using MySQL Command Line:**

```bash
mysql -u root -p
CREATE DATABASE religious_services CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### Step 8: Run Database Migrations

```bash
# Create all database tables
php artisan migrate
```

If you need sample data:

```bash
php artisan db:seed
```

### Step 9: Create Storage Link

```bash
php artisan storage:link
```

### Step 10: Build Frontend Assets

```bash
# Development build
npm run dev

# Or for production
npm run build
```

### Step 11: Start the Development Server

**Terminal 1 - Laravel Server:**

```bash
php artisan serve
```

The application will be available at: http://localhost:8000

**Terminal 2 - Vite Dev Server (for hot reload):**

```bash
npm run dev
```

---

## 🔧 Configuration Guide

### PHP Configuration (php.ini)

Edit `C:\php\php.ini` and enable these extensions (remove `;` at the start):

```ini
extension=curl
extension=fileinfo
extension=gd
extension=mbstring
extension=mysqli
extension=openssl
extension=pdo_mysql
extension=zip
extension=intl

; Set memory limit
memory_limit = 512M

; Set upload limits
upload_max_filesize = 10M
post_max_size = 10M
```

### MySQL Configuration

Create a user with proper permissions:

```sql
CREATE USER 'religious_user'@'localhost' IDENTIFIED BY 'your_password';
GRANT ALL PRIVILEGES ON religious_services.* TO 'religious_user'@'localhost';
FLUSH PRIVILEGES;
```

Then update `.env`:

```env
DB_USERNAME=religious_user
DB_PASSWORD=your_password
```

---

## 👥 Creating Test Users

### Option 1: Use Seeders (if available)

```bash
php artisan db:seed --class=UserSeeder
```

### Option 2: Manual Registration

1. Go to http://localhost:8000
2. Click "Register"
3. Fill in the form
4. Verify email (check `storage/logs/laravel.log` for verification link if MAIL_MAILER=log)

### Option 3: Use Artisan Tinker

```bash
php artisan tinker
```

Then run:

```php
// Create Admin User
$admin = new App\Models\User();
$admin->first_name = 'Admin';
$admin->last_name = 'User';
$admin->email = 'admin@test.com';
$admin->password = bcrypt('password123');
$admin->role = 'admin';
$admin->email_verified_at = now();
$admin->save();

// Create Priest User
$priest = new App\Models\User();
$priest->first_name = 'Father';
$priest->last_name = 'Dudz';
$priest->email = 'priest@test.com';
$priest->password = bcrypt('password123');
$priest->role = 'priest';
$priest->email_verified_at = now();
$priest->save();

// Create Adviser User
$adviser = new App\Models\User();
$adviser->first_name = 'Adviser';
$adviser->last_name = 'User';
$adviser->email = 'adviser@test.com';
$adviser->password = bcrypt('password123');
$adviser->role = 'adviser';
$adviser->email_verified_at = now();
$adviser->save();

// Exit tinker
exit
```

---

## 🔐 User Roles

The system has 5 roles:

-   **Admin** - Full system access
-   **Staff** - Manage reservations
-   **Adviser** - Approve organization requests
-   **Priest** - Confirm service assignments
-   **Requestor** - Submit reservation requests (default for new users)

---

## 🛠️ Common Commands

### Development

```bash
# Start Laravel server
php artisan serve

# Start Vite dev server (hot reload)
npm run dev

# Build assets for production
npm run build

# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Refresh database (WARNING: deletes all data)
php artisan migrate:fresh

# Refresh and seed
php artisan migrate:fresh --seed
```

### Queue Management

```bash
# Process queue jobs
php artisan queue:work

# Process queue in background
php artisan queue:listen
```

### Maintenance

```bash
# Clear application cache
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear config cache
php artisan config:clear

# Clear view cache
php artisan view:clear

# Create cache for faster loading
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📁 Important Directories

```
CAPSTONE-PROJECT/
├── app/                    # Application core
│   ├── Http/Controllers/   # Request handlers
│   ├── Models/            # Database models
│   ├── Services/          # Business logic
│   └── Mail/              # Email classes
├── config/                # Configuration files
├── database/              # Migrations & seeders
│   ├── migrations/        # Database structure
│   └── seeders/           # Sample data
├── public/                # Public assets (entry point)
├── resources/             # Frontend files
│   ├── views/             # Blade templates
│   ├── css/               # Stylwind CSS
│   └── js/                # JavaScript files
├── routes/                # Application routes
│   ├── web.php            # Web routes
│   └── auth.php           # Authentication routes
├── storage/               # File storage & logs
│   ├── app/               # Uploaded files
│   └── logs/              # Application logs
└── vendor/                # PHP dependencies (don't commit)
```

---

## 🐛 Troubleshooting

### Issue: "Class not found" errors

```bash
composer dump-autoload
php artisan clear-compiled
```

### Issue: Permission denied (storage/logs)

```bash
# Windows (PowerShell as Administrator)
icacls storage /grant Users:F /T
icacls bootstrap/cache /grant Users:F /T
```

### Issue: npm install fails

```bash
# Clear npm cache
npm cache clean --force

# Delete node_modules and package-lock.json
rm -rf node_modules package-lock.json

# Reinstall
npm install
```

### Issue: Database connection refused

1. Make sure MySQL is running
2. Check MySQL port (default: 3306)
3. Verify credentials in `.env`
4. Test connection:

```bash
php artisan tinker
DB::connection()->getPdo();
```

### Issue: Port 8000 already in use

```bash
# Use different port
php artisan serve --port=8001
```

### Issue: Assets not loading

```bash
# Make sure Vite is running
npm run dev

# Or build assets
npm run build

# Clear browser cache
# Hard refresh: Ctrl + Shift + R (Chrome/Firefox)
```

### Issue: 500 Internal Server Error

1. Check `storage/logs/laravel.log`
2. Enable debug mode in `.env`:
    ```env
    APP_DEBUG=true
    ```
3. Clear all caches:
    ```bash
    php artisan optimize:clear
    ```

---

## 📱 Accessing the Application

### URLs

-   **Main App**: http://localhost:8000
-   **Login**: http://localhost:8000/login
-   **Register**: http://localhost:8000/register

### Test Credentials (if you created them)

```
Admin:
Email: admin@test.com
Password: password123

Priest:
Email: priest@test.com
Password: password123

Adviser:
Email: adviser@test.com
Password: password123
```

---

## 🔄 Keeping Code Updated

### Pull Latest Changes

```bash
# Get latest code from GitHub
git pull origin new-feature

# Install any new dependencies
composer install
npm install

# Run new migrations
php artisan migrate

# Rebuild assets
npm run build

# Clear caches
php artisan optimize:clear
```

### Push Your Changes

```bash
# Check what changed
git status

# Add files
git add .

# Commit with message
git commit -m "Your descriptive message"

# Push to GitHub
git push origin new-feature
```

---

## 📚 Additional Resources

-   **Laravel Documentation**: https://laravel.com/docs/10.x
-   **Tailwind CSS**: https://tailwindcss.com/docs
-   **Vite**: https://vitejs.dev/guide/
-   **PHP Manual**: https://www.php.net/manual/en/

---

## � Using Docker without exposing secrets

This repo includes a simple `docker-compose.yml` for local development. To keep credentials out of version control:

1) Copy the example Docker env file and fill real values (do NOT commit the new file):

```powershell
Copy-Item .env.docker.example .env.docker
# Open .env.docker and set strong passwords
```

2) Compose loads credentials from `.env.docker` via `env_file` for the `db` and `phpmyadmin` services. The file `.env.docker` is git-ignored; only `.env.docker.example` is tracked.

3) Start the stack (optional):

```powershell
# Build app image and start containers
docker compose up -d --build

# View logs
docker compose logs -f
```

4) Production note: For real deployments, prefer Docker/Kubernetes secrets or your CI/CD provider’s secret manager instead of env files.

Summary: Put real secrets in `.env` (Laravel) and `.env.docker` (Compose), both are ignored by git. Commit only the `*.example` files with placeholders.

## �💡 Recommended VS Code Extensions (Optional)

If using VS Code:

1. **Laravel Extension Pack** - Laravel helpers
2. **PHP Intelephense** - PHP intellisense
3. **Tailwind CSS IntelliSense** - CSS autocomplete
4. **GitLens** - Git integration
5. **Better Comments** - Code documentation
6. **ESLint** - JavaScript linting
7. **Prettier** - Code formatting

---

## ⚡ Quick Start Checklist

-   [ ] Install PHP 8.2+
-   [ ] Install Composer
-   [ ] Install Node.js & npm
-   [ ] Install MySQL/XAMPP/Laragon
-   [ ] Install Git
-   [ ] Clone repository
-   [ ] Run `composer install`
-   [ ] Run `npm install`
-   [ ] Copy `.env.example` to `.env`
-   [ ] Configure database in `.env`
-   [ ] Run `php artisan key:generate`
-   [ ] Create database
-   [ ] Run `php artisan migrate`
-   [ ] Run `php artisan storage:link`
-   [ ] Run `npm run build`
-   [ ] Start server: `php artisan serve`
-   [ ] Access: http://localhost:8000

---

## 🆘 Need Help?

-   Check error logs: `storage/logs/laravel.log`
-   Laravel logs SQL queries when `APP_DEBUG=true`
-   Use `php artisan tinker` to test code interactively
-   Clear everything: `php artisan optimize:clear`

---

**Last Updated**: October 26, 2025
**Laravel Version**: 10.x
**PHP Version**: 8.2.29
**Node Version**: 18+
