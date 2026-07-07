# Deployment Guide - Hostinger Web Hosting

## 📋 Hostinger Requirements

### Recommended Hosting Plan

- **Minimum:** Business Web Hosting or higher
- **Recommended:** Cloud Startup or VPS for better performance
- **Why:** Laravel requires SSH access and Composer, available on Business plans and above

### What You Need

- [ ] Hostinger hosting account (Business plan or higher)
- [ ] Domain name (can purchase through Hostinger)
- [ ] FTP client (FileZilla) or SSH client
- [ ] Database credentials from hPanel
- [ ] SSL certificate (Free with Hostinger)

---

## 🚀 Deployment Methods

### Method 1: Using SSH (Recommended - Business/Cloud/VPS Plans)

This is the fastest and most reliable method for Hostinger Business, Cloud, or VPS plans.

**📌 Your Setup Details:**

- **Domain:** `darkcyan-whale-509153.hostingersite.com`
- **User:** `u269010508`
- **SSH Port:** `65002`
- **Laravel Path:** `~/public_html/student_registrationV2/slss-laravel`
- **SSH Command:** `ssh -p 65002 u269010508@31.97.97.131`

When you see `~/public_html/slss-laravel` in commands below, use the path above.

#### Step 1: Connect via SSH

SSH is already enabled for your account. Connect using:

```bash
ssh -p 65002 u269010508@31.97.97.131
```

Enter your Hostinger password when prompted.

**You're connected when you see:**

```
[u269010508@us-bos-web1685 ~]$
```

#### Step 3: Navigate to Web Root and Create Directory

```bash
# For shared hosting (Business plan):
cd ~/public_html

# For VPS/Cloud:
cd /home/username/public_html

# Or for subdomain:
cd ~/domains/yourdomain.com/public_html

# Create your project directory
mkdir -p student_registrationV2
cd student_registrationV2
```

#### Step 4: Upload Project Files

**Option A: Using Git (Recommended)**

```bash
# Navigate to your directory
cd ~/public_html/student_registrationV2

# Clone your repository
git clone https://github.com/yourusername/slss-app.git slss-laravel
cd slss-laravel

# Or upload via SCP from local machine:
# scp -P 65002 -r slss-laravel username@ssh.hostinger.com:~/public_html/student_registrationV2/
```

**Option B: Upload Archive**

```bash
# On your local machine, create archive:
cd /Users/keronlewis/Documents/SLSS-App/slss-laravel
tar -czf slss-deploy.tar.gz \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='storage/logs/*.log' \
  --exclude='.env' \
  .

# Upload to Hostinger:
scp -P 65002 slss-deploy.tar.gz username@ssh.hostinger.com:~/public_html/student_registrationV2/

# On server, extract:
cd ~/public_html/student_registrationV2
mkdir -p slss-laravel
tar -xzf slss-deploy.tar.gz -C slss-laravel
rm slss-deploy.tar.gz
```

#### Step 5: Install Composer Dependencies

```bash
cd ~/public_html/student_registrationV2/slss-laravel

# Check if Composer is available
composer --version

# If not available, install Composer:
# First, create bin directory if it doesn't exist
mkdir -p $HOME/bin

# Download and install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=$HOME/bin --filename=composer
rm composer-setup.php

# Add to PATH permanently
echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc

# Verify Composer installation
composer --version

# Install dependencies
composer install --optimize-autoloader --no-dev
```

**If you get a PHP version compatibility error:**

```
Error: maennchen/zipstream-php requires php-64bit ^8.3
Your PHP version (8.2.30) does not satisfy that requirement.
```

**Solution A: Upgrade to PHP 8.3** (Recommended if available)

1. Go to **hPanel → Advanced → PHP Configuration**
2. Select **PHP 8.3** from dropdown
3. Save changes
4. Run `composer install --optimize-autoloader --no-dev` again

**Solution B: Update composer.lock for PHP 8.2**

If PHP 8.3 is not available on your hosting plan:

```bash
# Remove existing lock file
rm composer.lock

# Update dependencies to match your PHP version
composer update --optimize-autoloader --no-dev
```

#### Step 6: Configure PHP Version

Hostinger allows PHP version selection:

1. Go to **hPanel → Advanced → PHP Configuration**
2. Select **PHP 8.1** or higher
3. Enable required extensions:
   - `mbstring`
   - `xml`
   - `pdo_mysql`
   - `gd`
   - `bcmath`
   - `curl`
   - `zip`

#### Step 7: Create Database

1. Go to **hPanel → Databases → MySQL Databases**
2. Click **Create New Database**
3. Database Name: `u123456789_slss` (Hostinger adds prefix)
4. Note the database name, username, and create a strong password
5. Click **Create**

**Database Credentials Example:**

```
DB_HOST=localhost
DB_DATABASE=u123456789_slss
DB_USERNAME=u123456789_user
DB_PASSWORD=YourSecurePassword123!
```

#### Step 8: Configure Environment

```bash
cd ~/public_html/student_registrationV2/slss-laravel

# Copy environment file
cp .env.example .env

# Edit with nano or vim
nano .env
```

**Update these values in .env:**

```env
APP_NAME="SLSS Student Management"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://darkcyan-whale-509153.hostingersite.com

# Database (use credentials from hPanel)
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u269010508_slss
DB_USERNAME=u269010508_user
DB_PASSWORD=YourSecurePassword123!

# Session & Cache
SESSION_DRIVER=database
CACHE_DRIVER=file
QUEUE_CONNECTION=database

# Mail (optional - configure later)
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"
```

Save and exit (`CTRL+X`, then `Y`, then `Enter`)

#### Step 9: Generate Application Key

```bash
php artisan key:generate
```

#### Step 10: Run Migrations

```bash
# Run database migrations
php artisan migrate --force

# Seed default users
php artisan db:seed --force

# Create storage link
php artisan storage:link
```

#### Step 11: Set Permissions

```bash
# Set correct permissions
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage/app storage/framework storage/logs
```

#### Step 12: Configure Document Root

**Important:** Laravel's public directory must be your document root.

Your app is located at: `public_html/student_registrationV2/slss-laravel`

**Option A: Using .htaccess Redirect (if Laravel is in subdirectory)**

Create/edit `.htaccess` in your `public_html` directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ student_registrationV2/slss-laravel/public/$1 [L]
</IfModule>
```

This redirects all traffic to your Laravel public folder.

**Option B: Change Document Root via hPanel (Recommended)**

1. Go to **hPanel → Websites**
2. Find your domain: `darkcyan-whale-509153.hostingersite.com`
3. Click **Manage**
4. Scroll to **Website root** or **Document Root** setting
5. Change from:

   ```
   public_html
   ```

   To:
   ```
   public_html/student_registrationV2/slss-laravel/public
   ```
6. Click **Save** or **Update**
7. Wait 2-3 minutes for changes to take effect

Then access your site at: `https://darkcyan-whale-509153.hostingersite.com`

**Option C: Use Subdomain (When You Have a Custom Domain)**

When you add your custom domain later:

1. Go to **hPanel → Domains → Subdomains**
2. Create subdomain: `students.yourdomain.com` or `portal.yourdomain.com`
3. Set document root to: `public_html/student_registrationV2/slss-laravel/public`
4. Save and wait for DNS propagation (5-10 minutes)

For now, use the Hostinger temporary domain: `darkcyan-whale-509153.hostingersite.com`

**Option D: Access via subdirectory (Quick test - not for production)**

Temporarily access via:

```
https://darkcyan-whale-509153.hostingersite.com/student_registrationV2/slss-laravel/public
```

Then use Option A or B to make it the root domain.

**Note:** Your Hostinger domain is `darkcyan-whale-509153.hostingersite.com`. Once you connect your custom domain, update these URLs accordingly.

#### Step 13: Optimize for Production

```bash
cd ~/public_html/student_registrationV2/slss-laravel

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

#### Step 14: Enable SSL (HTTPS)

1. Go to **hPanel → Security → SSL**
2. Enable **Free SSL Certificate** for your domain
3. Wait 5-10 minutes for SSL to activate
4. Force HTTPS in `.htaccess` (Laravel already handles this)

#### Step 15: Test Your Application

**Test URL (before document root configuration):**

```
https://darkcyan-whale-509153.hostingersite.com/student_registrationV2/slss-laravel/public
```

**After document root configuration:**

```
https://darkcyan-whale-509153.hostingersite.com
```

Default login:

- Email: `admin@slss.edu.tt`
- Password: `admin123`

**⚠️ Change default passwords immediately!**

---

### Method 2: Using FTP/File Manager (Premium/Business Plans without SSH)

If SSH is not available, use this method.

#### Step 1: Prepare Files Locally

```bash
# On your local machine
cd /Users/keronlewis/Documents/SLSS-App/slss-laravel

# Install dependencies locally
composer install --optimize-autoloader --no-dev

# Create ZIP file
zip -r slss-app.zip . \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "storage/logs/*.log" \
  -x ".env"
```

#### Step 2: Upload via hPanel File Manager

1. Login to **Hostinger hPanel**
2. Go to **Files → File Manager**
3. Navigate to `domains/yourdomain.com/public_html` or `public_html`
4. Click **Upload Files**
5. Upload `slss-app.zip`
6. Right-click → **Extract**

**Or use FTP:**

1. Go to **hPanel → Files → FTP Accounts**
2. Note FTP credentials or create new account
3. Use FileZilla:
   - Host: `ftp.yourdomain.com` or IP
   - Username: Your FTP username
   - Password: Your FTP password
   - Port: `21`
4. Upload all files to `public_html/slss-laravel`

#### Step 3: Create Database via hPanel

1. Go to **hPanel → Databases → MySQL Databases**
2. Create new database
3. Note database name and credentials

#### Step 4: Configure .env via File Manager

1. In File Manager, navigate to your Laravel directory
2. Copy `.env.example` → `.env`
3. Right-click `.env` → **Edit**
4. Update database credentials and app settings
5. Save

#### Step 5: Run Artisan Commands via PHP

Since no SSH access, create a temporary PHP file `install.php` in public directory:

```php
<?php
// install.php - DELETE THIS FILE AFTER USE!

define('LARAVEL_START', microtime(true));

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "<pre>";

// Generate key
echo "Generating application key...\n";
$kernel->call('key:generate', ['--force' => true]);

// Run migrations
echo "Running migrations...\n";
$kernel->call('migrate', ['--force' => true]);

// Seed database
echo "Seeding database...\n";
$kernel->call('db:seed', ['--force' => true]);

// Create storage link
echo "Creating storage link...\n";
$kernel->call('storage:link');

// Cache config
echo "Caching configuration...\n";
$kernel->call('config:cache');
$kernel->call('route:cache');
$kernel->call('view:cache');

echo "\nInstallation complete!\n";
echo "DELETE THIS FILE NOW!\n";
echo "</pre>";
```

1. Upload `install.php` to `public_html/slss-laravel/public/`
2. Visit: `https://yourdomain.com/install.php`
3. Wait for completion
4. **Delete `install.php` immediately for security**

#### Step 6: Set Permissions via File Manager

1. Right-click `storage` folder → **Permissions**
2. Set to `755` or `775`
3. Repeat for `bootstrap/cache`

#### Step 7: Configure Document Root (Same as Method 1 Step 12)

---

## 🔒 Security Hardening for Hostinger

### 1. Protect .env File

Create `.htaccess` in Laravel root (not public):

```apache
# Protect .env
<Files .env>
    Order allow,deny
    Deny from all
</Files>
```

### 2. Disable Directory Listing

In `public/.htaccess`, ensure:

```apache
Options -Indexes
```

### 3. Restrict Access to Sensitive Directories

Add to root `.htaccess`:

```apache
# Deny access to sensitive directories
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(storage|vendor|bootstrap|database|config|resources)/ - [F,L]
</IfModule>
```

### 4. Enable Cloudflare (Optional but Recommended)

Hostinger integrates with Cloudflare:

1. Go to **hPanel → Security → Cloudflare**
2. Enable Cloudflare
3. Benefits: DDoS protection, CDN, caching

### 5. Regular Backups

1. Go to **hPanel → Files → Backups**
2. Enable **Automatic Backups** (weekly recommended)
3. Or manually download backups before major updates

### 6. Update PHP Regularly

1. Check **hPanel → Advanced → PHP Configuration**
2. Use latest stable PHP version (8.1+)

---

## 🗄️ Database Management on Hostinger

### Accessing phpMyAdmin

1. Go to **hPanel → Databases → MySQL Databases**
2. Click **phpMyAdmin** next to your database
3. Login with database credentials

### Backup Database

**Via hPanel:**

1. Go to **phpMyAdmin**
2. Select your database
3. Click **Export** → **Go**

**Via SSH (if available):**

```bash
# Export database
mysqldump -u u123456789_user -p u123456789_slss > backup_$(date +%Y%m%d).sql

# Import database
mysql -u u123456789_user -p u123456789_slss < backup_20250107.sql
```

### Import Existing Data

**Method 1: Via phpMyAdmin**

1. Open phpMyAdmin
2. Select database
3. Click **Import**
4. Choose your `.sql` file
5. Click **Go**

**Method 2: Via SSH**

```bash
mysql -u u123456789_user -p u123456789_slss < slss.sql
```

---

## ⚡ Performance Optimization for Hostinger

### 1. Enable OPcache

1. Go to **hPanel → Advanced → PHP Configuration**
2. Enable **OPcache**
3. Set recommended values:
   - `opcache.enable=1`
   - `opcache.memory_consumption=128`
   - `opcache.max_accelerated_files=10000`

### 2. Use Laravel Caching

```bash
# If SSH is available:
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Clear cache when needed:
php artisan cache:clear
```

### 3. Optimize Images

Ensure student photos are optimized:

- Max 500KB per photo
- 800x800px max resolution
- Use JPEG format

### 4. Enable Gzip Compression

Add to `public/.htaccess`:

```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css application/javascript application/json
</IfModule>
```

### 5. Browser Caching

Already in Laravel's `.htaccess`, verify:

```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## 🐛 Troubleshooting Hostinger-Specific Issues

### Issue: 500 Internal Server Error

**Solutions:**

1. **Check error logs:**

   ```bash
   # Via SSH:
   tail -f ~/public_html/slss-laravel/storage/logs/laravel.log

   # Via hPanel:
   # Go to: Files → File Manager → Navigate to storage/logs/laravel.log
   ```
2. **Check PHP error logs:**

   - hPanel → Advanced → Error Logs
3. **Verify .htaccess:**

   ```bash
   # Ensure mod_rewrite is working
   # Check public/.htaccess has:
   ```

   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteBase /
       # ... rest of rules
   </IfModule>
   ```
4. **Check file permissions:**

   ```bash
   chmod -R 755 storage bootstrap/cache
   ```

### Issue: "Application key not set"

**Solution:**

```bash
# Via SSH:
php artisan key:generate

# Without SSH:
# Use install.php script or manually add 32-character random key to .env:
APP_KEY=base64:YourRandomGeneratedKeyHere
```

### Issue: Database connection failed

**Solutions:**

1. Verify credentials in `.env` match hPanel → Databases
2. Ensure database user has privileges:
   - Go to phpMyAdmin
   - Check user privileges
3. Try `127.0.0.1` instead of `localhost`:
   ```env
   DB_HOST=127.0.0.1
   ```

### Issue: Storage link not working

**Solution:**

```bash
# Via SSH:
php artisan storage:link

# Without SSH - create symlink via PHP:
# Create link.php in public/:
```

```php
<?php
$target = '../storage/app/public';
$link = 'storage';
symlink($target, $link);
echo "Storage linked!";
// DELETE THIS FILE AFTER USE
?>
```

### Issue: Composer not found

**Solution:**

```bash
# Create bin directory first
mkdir -p $HOME/bin

# Install Composer locally:
cd ~
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=$HOME/bin --filename=composer
rm composer-setup.php

# Add to PATH:
echo 'export PATH="$HOME/bin:$PATH"' >> ~/.bashrc
source ~/.bashrc

# Verify:
composer --version
```

### Issue: Composer install dir does not exist

**Error:**

```
The defined install dir (/home/u269010508/bin) does not exist.
```

**Solution:**

```bash
# Create the directory first
mkdir -p $HOME/bin

# Then install Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=$HOME/bin --filename=composer
rm composer-setup.php
```

### Issue: PHP version compatibility error

**Error:**

```
maennchen/zipstream-php requires php-64bit ^8.3
Your PHP version (8.2.30) does not satisfy that requirement.
```

**Solutions:**

**Option 1: Upgrade PHP (Recommended)**

1. Go to **hPanel → Advanced → PHP Configuration**
2. Select **PHP 8.3** or higher
3. Click **Save**
4. Wait 2-3 minutes for changes to apply
5. Verify: `php -v`
6. Run: `composer install --optimize-autoloader --no-dev`

**Option 2: Update composer.lock for current PHP version**

```bash
# Navigate to project
cd ~/public_html/slss-laravel

# Remove lock file
rm composer.lock

# Update all dependencies to match PHP 8.2
composer update --optimize-autoloader --no-dev

# This will regenerate composer.lock with PHP 8.2 compatible versions
```

**Option 3: Use PHP 8.3 via command line (if available but not default)**

```bash
# Check available PHP versions
ls /opt/alt/php*/usr/bin/php

# Use PHP 8.3 specifically for composer
/opt/alt/php83/usr/bin/php $(which composer) install --optimize-autoloader --no-dev

# Or alias it temporarily
alias php='/opt/alt/php83/usr/bin/php'
composer install --optimize-autoloader --no-dev
```

### Issue: Security advisories blocking installation

**Error:**

```
Your requirements could not be resolved to an installable set of packages.
Problem 1
- Root composer.json requires laravel/framework ^10.0, found laravel/framework[v10.0.0, ..., 10.50.2]
  but these were not loaded, because they are affected by security advisories
```

**This happens when:** Composer 2.9+ blocks packages with known security vulnerabilities.

**Solutions:**

**Option 1: Upgrade to Laravel 11 (Recommended - Already done in composer.json)**

The project has been updated to Laravel 11 which addresses security issues:

```bash
cd ~/public_html/slss-laravel

# Install with updated dependencies
composer install --optimize-autoloader --no-dev
```

**Option 2: Temporarily disable security audit (NOT recommended for production)**

Only use this if you absolutely need Laravel 10:

```bash
# Disable audit temporarily
composer install --optimize-autoloader --no-dev --no-audit

# Or configure composer to not block insecure packages
composer config --no-plugins allow-plugins.composer/package-versions-deprecated true
composer config audit.abandoned ignore
```

**Option 3: Install from existing composer.lock (if you have one)**

```bash
# Upload your working composer.lock file from local/development
# Then run:
composer install --optimize-autoloader --no-dev --ignore-platform-reqs
```

### Issue: Permission denied errors

**Solutions:**

```bash
# Set correct ownership (if on VPS):
chown -R username:username ~/public_html/slss-laravel

# Set permissions:
find ~/public_html/slss-laravel -type f -exec chmod 644 {} \;
find ~/public_html/slss-laravel -type d -exec chmod 755 {} \;
chmod -R 775 ~/public_html/slss-laravel/storage
chmod -R 775 ~/public_html/slss-laravel/bootstrap/cache
```

### Issue: PDF generation not working

**Solutions:**

1. Check if images exist:

   ```bash
   ls -la public/images/
   # Should see: successlogo.png, OfficialDocument1.png, noimage.jpg
   ```
2. Check DomPDF dependencies:

   ```bash
   composer require barryvdh/laravel-dompdf
   ```
3. Check PHP extensions:

   - hPanel → Advanced → PHP Configuration
   - Enable: `gd`, `mbstring`

### Issue: Email not sending

**Solution:**

Configure SMTP in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=your-email@yourdomain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls
```

Create email account at: hPanel → Emails → Email Accounts

### Issue: Session/Cache not working

**Solution:**

```bash
# Clear all caches:
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Ensure storage is writable:
chmod -R 775 storage/framework/sessions
chmod -R 775 storage/framework/cache
```

### Issue: Site loads slowly

**Solutions:**

1. Enable OPcache (see Performance section)
2. Use Laravel caching
3. Optimize database queries
4. Consider upgrading to Cloud hosting
5. Enable Cloudflare via hPanel

---

## 📊 Monitoring & Maintenance

### Check Disk Usage

```bash
# Via SSH:
du -sh ~/public_html/slss-laravel
du -sh ~/public_html/slss-laravel/storage

# Via hPanel:
# Dashboard shows disk usage
```

### Clear Old Logs

```bash
# Via SSH:
cd ~/public_html/slss-laravel/storage/logs
rm laravel-*.log  # Keep latest only

# Or use Laravel's log rotation (add to Scheduler)
```

### Update Laravel

```bash
# Via SSH:
cd ~/public_html/slss-laravel

# Backup first!
composer update

# Run migrations if needed
php artisan migrate --force

# Clear caches
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Monitor Error Logs

Regularly check:

- `storage/logs/laravel.log`
- hPanel → Advanced → Error Logs

---

## 🎯 Post-Deployment Checklist

- [ ] SSL certificate installed and working (HTTPS)
- [ ] Document root points to `public` directory
- [ ] Database connected and migrations completed
- [ ] Default admin password changed
- [ ] File permissions set correctly (755/775)
- [ ] `.env` file is protected
- [ ] Storage link created and working
- [ ] Photos uploading successfully
- [ ] PDF generation working
- [ ] Email sending configured (if needed)
- [ ] Backups enabled in hPanel
- [ ] OPcache enabled
- [ ] Laravel caching enabled
- [ ] Cloudflare enabled (optional)
- [ ] Error logs accessible
- [ ] Test CSV import functionality

---

## 📞 Hostinger Support

If issues persist:

1. **Hostinger Live Chat:** 24/7 support via hPanel
2. **Email Support:** support@hostinger.com
3. **Knowledge Base:** https://support.hostinger.com/
4. **Community Forum:** https://www.hostinger.com/forum

---

## 🚀 Upgrading Your Hosting Plan

If performance is an issue, consider upgrading:

- **Business → Cloud Startup:** Better resources, same interface
- **Cloud Startup → Cloud Professional:** More CPU/RAM
- **Cloud → VPS:** Full control, root access

Benefits of upgrading:

- More PHP workers
- Better resource allocation
- Faster database queries
- Support for more concurrent users

---

**Deployment Complete!** 🎉

Your SLSS Student Management System is now live on Hostinger!

Visit: `https://yourdomain.com`

Login with admin credentials and **change your password immediately**.
