# Hostinger Deployment Guide - SLSS Laravel Student Management System

## 🔍 Code Audit Summary

### ✅ Audit Completed: January 7, 2026

All code has been audited and optimized for Hostinger's shared hosting environment.

---

## 📋 Issues Fixed

### 1. **composer.json - Duplicate Config Block** ✅ FIXED
- **Issue**: Duplicate `config` block causing potential JSON parsing issues
- **Fix**: Merged duplicate blocks into single configuration
- **Status**: Resolved

### 2. **PHP 8.2 Compatibility** ✅ VERIFIED
- **Status**: All dependencies compatible with PHP 8.2.30 (Hostinger's version)
- **Laravel Framework**: ^11.0 ✅
- **DomPDF**: ^3.0 ✅
- **Maatwebsite Excel**: ^3.1 ✅
- **Intervention Image**: ^3.0 ✅

### 3. **Production Environment Configuration** ✅ OPTIMIZED
- **`.env.example`**: Updated with Hostinger-specific values
- **APP_ENV**: Changed from `local` to `production`
- **APP_DEBUG**: Disabled for security (`false`)
- **LOG_LEVEL**: Changed from `debug` to `error`
- **Database**: Pre-configured for Hostinger MySQL

### 4. **Code Structure** ✅ VERIFIED
- **No hardcoded paths**: All paths use Laravel helpers
- **No localhost references**: All configs use environment variables
- **Service layer architecture**: Properly implemented
- **Form requests**: Validation properly separated
- **Migrations**: Compatible with MySQL 5.7+

---

## 🚀 Deployment Instructions

### Prerequisites

Before deployment, ensure you have:

1. **Hostinger Account Access**
   - Domain: `darkcyan-whale-509153.hostingersite.com`
   - Username: `u269010508`
   - SSH Access: Port `65002`

2. **Database Created in hPanel**
   - Database name: `u269010508_slss`
   - Database user: `u269010508_user`
   - Password: (set in hPanel)

3. **Git Repository**
   - Code committed to git repository
   - Remote repository URL ready (GitHub, GitLab, Bitbucket, etc.)

---

## 🎯 Deployment Method: Git (Recommended)

### Why Git Deployment?
- ✅ Easier updates (just `git pull`)
- ✅ Version control and rollback capability
- ✅ Only transfers changed files
- ✅ Composer installs dependencies on server
- ✅ Follows industry best practices

---

## 📦 Step 1: Initial Setup on Hostinger

Connect to Hostinger via SSH:

```bash
ssh -p 65002 u269010508@31.97.97.131
```

### 1.1 Setup Git Repository

```bash
# Navigate to web root
cd ~/public_html/student_registrationV2

# Clone your repository
# Replace with your actual git repository URL
git clone YOUR_GIT_REPOSITORY_URL slss-laravel

# Example:
# git clone https://github.com/yourusername/slss-laravel.git slss-laravel
# or
# git clone git@github.com:yourusername/slss-laravel.git slss-laravel

cd slss-laravel
```

**Note:** If using private repository, you may need to:
- Set up SSH keys: `ssh-keygen -t ed25519 -C "u269010508@hostinger"`
- Add public key to GitHub/GitLab: `cat ~/.ssh/id_ed25519.pub`

### 1.2 Install Composer (if not already installed)

```bash
# Check if composer exists
which composer

# If not found, install composer
cd ~
mkdir -p bin
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=$HOME/bin --filename=composer
php -r "unlink('composer-setup.php');"

# Add to PATH (add to ~/.bashrc for persistence)
export PATH="$HOME/bin:$PATH"

# Verify installation
composer --version
```

### 1.3 Install Dependencies

```bash
cd ~/public_html/student_registrationV2/slss-laravel

# Install production dependencies
composer install --optimize-autoloader --no-dev
```

---

## 🔧 Step 2: Server Configuration

### 2.1 Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Edit .env with nano
nano .env
```

**Update these values in `.env`:**

```ini
APP_NAME="SLSS Student Management"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://darkcyan-whale-509153.hostingersite.com

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u269010508_slss
DB_USERNAME=u269010508_user
DB_PASSWORD=YOUR_DATABASE_PASSWORD_FROM_HPANEL

SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
QUEUE_CONNECTION=sync
```

**Press:** `CTRL+O` (save), `ENTER`, `CTRL+X` (exit)

### 2.2 Generate Application Key

```bash
php artisan key:generate
```

### 2.3 Set File Permissions

```bash
# Set proper permissions for Laravel
chmod -R 775 storage bootstrap/cache

# Storage directories should already exist from git (.gitkeep files)
# But if needed:
mkdir -p storage/framework/sessions
mkdir -p storage/framework/views
mkdir -p storage/framework/cache
mkdir -p storage/logs
```

### 2.4 Create Storage Symlink

```bash
php artisan storage:link
```

### 2.5 Run Database Migrations

```bash
# Run migrations (creates tables)
php artisan migrate --force

# Seed default admin users
php artisan db:seed --force
```

### 2.6 Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache
```

---

## 🌐 Step 3: Configure Document Root in hPanel

### 3.1 Access hPanel
1. Log in to Hostinger hPanel
2. Go to **Advanced** → **Domain Configuration**

### 3.2 Update Document Root
1. Find domain: `darkcyan-whale-509153.hostingersite.com`
2. Click **Manage**
3. Update **Document Root** to:
   ```
   public_html/student_registrationV2/slss-laravel/public
   ```
4. Click **Save**

### 3.3 Verify .htaccess

The `.htaccess` file should already exist in `public/` from git (it's tracked in version control).

Verify it exists:

```bash
cd ~/public_html/student_registrationV2/slss-laravel/public
ls -la .htaccess
```

If it's missing, check it out from git:
```bash
cd ~/public_html/student_registrationV2/slss-laravel
git checkout public/.htaccess
```

---

## ✅ Step 4: Test Deployment

### 4.1 Access Application

Open browser and navigate to:
```
https://darkcyan-whale-509153.hostingersite.com
```

**Expected Result:** Login page should appear

### 4.2 Test Login

Use default admin credentials:

- **Email:** `admin@slss.edu.tt`
- **Password:** `admin123`

### 4.3 Verify Features

After login, test:
- ✅ View students list
- ✅ Add new student
- ✅ Edit student
- ✅ Generate PDF
- ✅ Import CSV
- ✅ Bulk PDF export

---

## 🔧 Troubleshooting

### Issue: 500 Internal Server Error

**Solution 1: Check Laravel Logs**
```bash
tail -50 ~/public_html/student_registrationV2/slss-laravel/storage/logs/laravel.log
```

**Solution 2: Check File Permissions**
```bash
chmod -R 775 storage bootstrap/cache
```

**Solution 3: Clear All Caches**
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

**Solution 4: Verify .env File**
```bash
cat .env | grep -E "DB_|APP_KEY"
```

Ensure:
- `APP_KEY` is set (not empty)
- Database credentials are correct

---

### Issue: Blank Page

**Check PHP Error Logs:**
```bash
tail -50 ~/logs/error_log
```

**Enable Debug Mode Temporarily:**
```bash
# Edit .env
nano .env

# Change:
APP_DEBUG=true

# Save and test, then change back to false
```

---

### Issue: Database Connection Error

**Verify Database Exists:**
1. Log in to hPanel
2. Go to **Databases** → **MySQL Databases**
3. Verify `u269010508_slss` exists
4. Verify user `u269010508_user` has permissions

**Test Connection:**
```bash
mysql -u u269010508_user -p u269010508_slss -e "SELECT 1"
```

---

### Issue: Images/PDFs Not Loading

**Solution:**
```bash
# Recreate storage link
rm -f public/storage
php artisan storage:link

# Set proper permissions
chmod -R 775 storage
```

---

## 📊 Database Information

### Default Users (Created by Seeder)

| Role | Email | Password | Permissions |
|------|-------|----------|-------------|
| Admin | admin@slss.edu.tt | admin123 | Full access |
| Staff | staff@slss.edu.tt | staff123 | Create, edit, import |
| Viewer | viewer@slss.edu.tt | viewer123 | View only |

**⚠️ IMPORTANT:** Change these passwords after first login!

---

## 🔐 Security Checklist

After deployment, verify:

- [ ] `APP_DEBUG=false` in production
- [ ] `APP_ENV=production`
- [ ] Strong `APP_KEY` generated
- [ ] Database password is secure
- [ ] Default user passwords changed
- [ ] `.env` file not accessible from web
- [ ] Storage permissions set correctly (775, not 777)
- [ ] All caches cleared and regenerated
- [ ] HTTPS enabled (Hostinger provides SSL)

---

## 📁 Directory Structure on Server

```
~/public_html/student_registrationV2/slss-laravel/
├── app/                      # Application code
├── bootstrap/                # Framework bootstrap
├── config/                   # Configuration files
├── database/                 # Migrations and seeders
├── public/                   # ← Document root points here
│   ├── index.php            # Entry point
│   ├── .htaccess            # Apache rewrite rules
│   ├── images/              # School logos
│   └── storage/             # Symlink to storage/app/public
├── resources/               # Views and assets
├── routes/                  # Route definitions
├── storage/                 # Logs, cache, uploads
├── vendor/                  # Composer dependencies
├── .env                     # Environment configuration
└── artisan                  # CLI tool
```

---

## 🔄 Updating Application (Git Workflow)

### On Your Local Mac

1. **Make your changes and commit to git:**

```bash
cd /Users/keronlewis/Documents/SLSS-App/slss-laravel

# Stage all changes
git add .

# Commit with message
git commit -m "Description of changes made"

# Push to remote repository
git push origin master
```

### On Hostinger Server

2. **Pull updates and deploy:**

```bash
# Connect to server
ssh -p 65002 u269010508@31.97.97.131

# Navigate to application
cd ~/public_html/student_registrationV2/slss-laravel

# Backup .env (just in case)
cp .env .env.backup

# Pull latest changes from git
git pull origin master

# Install/update dependencies (if composer.json changed)
composer install --optimize-autoloader --no-dev

# Run new migrations (if any)
php artisan migrate --force

# Clear and regenerate caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Quick Update Command

For convenience, create an update script:

```bash
# Create update script
cat > ~/update-slss.sh << 'EOF'
#!/bin/bash
cd ~/public_html/student_registrationV2/slss-laravel
echo "Pulling latest changes..."
git pull origin master
echo "Installing dependencies..."
composer install --optimize-autoloader --no-dev
echo "Running migrations..."
php artisan migrate --force
echo "Clearing caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
echo "Regenerating caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "✅ Update complete!"
EOF

chmod +x ~/update-slss.sh
```

Then for future updates, just run:
```bash
~/update-slss.sh
```

### Rolling Back Changes

If something goes wrong:

```bash
cd ~/public_html/student_registrationV2/slss-laravel

# See recent commits
git log --oneline -10

# Rollback to previous commit
git reset --hard COMMIT_HASH

# Or rollback by 1 commit
git reset --hard HEAD~1

# Clear and recache
php artisan config:clear
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📁 Git Configuration & What's Tracked

### ✅ Files Tracked in Git (Committed)

The following files **ARE** committed to version control:

- ✅ Application code (`app/`, `config/`, `routes/`)
- ✅ Database migrations and seeders (`database/`)
- ✅ Blade templates (`resources/views/`)
- ✅ Public assets (`public/images/`, `public/css/`, `public/js/`)
- ✅ `.htaccess` file in `public/` directory
- ✅ `.env.example` (template for environment config)
- ✅ `composer.json` and `composer.lock`
- ✅ `.gitkeep` files (preserve empty directory structure)
- ✅ Documentation files (README.md, etc.)

### ❌ Files NOT Tracked (Ignored)

The following files **ARE NOT** committed (in `.gitignore`):

- ❌ `.env` - Environment-specific configuration
- ❌ `vendor/` - Composer dependencies (installed on server)
- ❌ `node_modules/` - NPM dependencies (if using frontend build)
- ❌ `.htaccess*` outside public folder - Environment-specific
- ❌ Storage files:
  - `storage/logs/*.log` - Log files
  - `storage/framework/cache/*` - Cache files
  - `storage/framework/sessions/*` - Session files
  - `storage/framework/views/*` - Compiled views
  - `storage/app/public/*` - User uploads
- ❌ `bootstrap/cache/*` - Bootstrap cache
- ❌ `*.sql` - Database dumps
- ❌ OS files (`.DS_Store`, `Thumbs.db`)
- ❌ IDE files (`.idea/`, `.vscode/`)

### 🔍 Why This Matters

**Environment files (`.env`, `.htaccess`)** are not tracked because:
- They contain environment-specific settings (database credentials, app keys)
- Local development settings differ from production
- Security: prevents accidental commit of passwords/keys

**Vendor directory** is not tracked because:
- It's regenerated from `composer.json` via `composer install`
- Reduces repository size significantly
- Ensures server uses correct PHP version dependencies

**Storage/cache files** are not tracked because:
- They're regenerated automatically
- User uploads are environment-specific
- Log files would bloat the repository

### 📝 Viewing What's Ignored

To see what files are currently ignored:

```bash
git status --ignored
```

To check if a specific file is tracked:

```bash
git ls-files | grep filename
```

---

## 📞 Support

**SSH Connection Details:**
- Host: `31.97.97.131`
- Port: `65002`
- User: `u269010508`
- Command: `ssh -p 65002 u269010508@31.97.97.131`

**Database Details:**
- Host: `localhost`
- Database: `u269010508_slss`
- User: `u269010508_user`

**Application URL:**
- Primary: `https://darkcyan-whale-509153.hostingersite.com`

---

## ✅ Final Verification Checklist

Before considering deployment complete:

- [ ] Application loads without errors
- [ ] Login works with default credentials
- [ ] Can view student list
- [ ] Can create new student
- [ ] Can edit existing student
- [ ] Can delete student (admin only)
- [ ] PDF generation works
- [ ] Bulk PDF export works
- [ ] CSV import works
- [ ] Images upload and display correctly
- [ ] Student passport photos display
- [ ] All 3 user roles work correctly (admin, staff, viewer)
- [ ] Database connection stable
- [ ] No errors in Laravel logs
- [ ] Production optimizations active (cached configs)

---

**Deployment Prepared:** January 7, 2026
**Laravel Version:** 11.54.0
**PHP Version Required:** 8.2+
**Hostinger PHP Version:** 8.2.30 ✅

---

🎉 **Your application is ready for deployment to Hostinger!**
