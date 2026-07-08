# Deployment Update Guide - Security Enhancements

**Date:** July 7, 2026
**Purpose:** Deploy security enhancements to production server
**Estimated Time:** 5-10 minutes

---

## 📋 Pre-Deployment Checklist

Before starting, ensure you have:

- [X] SSH access to Hostinger server
- [X] Application code committed to git repository
- [X] Git repository pushed to remote (GitHub/GitLab)
- [X] Backup of current production database (recommended)

---

## 🚀 Step-by-Step Deployment Instructions

### Step 1: Connect to Server

```bash
ssh -p 65002 u269010508@31.97.97.131
```

### Step 2: Navigate to Application Directory

```bash
cd ~/public_html/student_registrationV2/slss-laravel
```

### Step 3: Check Current Status

```bash
# View current branch and status
git status

# View current files
ls -la
```

### Step 4: Pull Latest Changes

```bash
# Fetch and pull latest changes from repository
git pull origin master
```

**Expected Output:**

```
remote: Enumerating objects: X, done.
remote: Counting objects: 100% (X/X), done.
Updating abc1234..def5678
Fast-forward
 .env.example                              | XX ++++++
 app/Console/Commands/ResetUserPassword.php | XX +++++++++
 app/Http/Kernel.php                       | X +
 app/Http/Middleware/SecurityHeaders.php   | XX +++++++++
 database/seeders/DatabaseSeeder.php       | XX +++++---
 routes/web.php                            | XX +-
 SECURITY_AUDIT_REPORT.md                  | XXX ++++++++++
 X files changed, XXX insertions(+), XX deletions(-)
 create mode 100644 app/Console/Commands/ResetUserPassword.php
 create mode 100644 app/Http/Middleware/SecurityHeaders.php
 create mode 100644 SECURITY_AUDIT_REPORT.md
```

### Step 5: Install/Update Dependencies

**Note:** Since `composer.lock` was updated, we need to ensure dependencies are in sync.

```bash
# Install dependencies (should be quick as most are already installed)
composer install --optimize-autoloader --no-dev
```

**Expected Output:**

```
Installing dependencies from lock file (including require-dev)
Verifying lock file contents can be installed on current platform.
Nothing to install, update or remove
Generating optimized autoload files
```

### Step 6: Clear All Caches

```bash
# Clear application caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

**Expected Output:**

```
Configuration cache cleared successfully.
Application cache cleared successfully.
Route cache cleared successfully.
Compiled views cleared successfully.
```

### Step 7: Regenerate Optimized Caches

```bash
# Regenerate caches for production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Expected Output:**

```
Configuration cached successfully.
Route cache cleared successfully.
Routes cached successfully.
Blade templates cached successfully.
```

### Step 8: Verify New Security Features

#### 8.1 Check Security Headers Middleware

```bash
# Verify middleware file exists
ls -la app/Http/Middleware/SecurityHeaders.php
```

**Expected Output:**

```
-rw-r--r-- 1 u269010508 u269010508 XXXX Month Day HH:MM app/Http/Middleware/SecurityHeaders.php
```

#### 8.2 Check Password Reset Command

```bash
# Verify command is available
php artisan list | grep "user:reset"
```

**Expected Output:**

```
user:reset-password       Reset a user's password. Usage: php artisan user:reset-password [email] [--password=new_password]
```

#### 8.3 Check Routes with Rate Limiting

```bash
# View routes to confirm rate limiting
php artisan route:list --columns=Method,URI,Middleware | grep -E "(login|import)"
```

**Expected Output:**

```
POST     | login        | throttle:5,1,web,...
POST     | import       | throttle:10,1,auth,can:edit-students,...
```

### Step 9: Reset Default Passwords (CRITICAL SECURITY STEP)

**⚠️ IMPORTANT:** You must change the default passwords immediately!

#### Option A: Interactive Mode (Recommended)

```bash
php artisan user:reset-password
```

**Follow the prompts:**

1. Select user email from the table
2. Enter new strong password (min 8 characters)
3. Confirm password
4. Confirm the action

**Repeat for all three users:**

- admin@slss.edu.tt
- staff@slss.edu.tt
- viewer@slss.edu.tt

#### Option B: Direct Mode (Faster)

```bash
# Reset admin password
php artisan user:reset-password admin@slss.edu.tt --password="YourStrongPassword123!"

# Reset staff password
php artisan user:reset-password staff@slss.edu.tt --password="YourStrongPassword456!"

# Reset viewer password
php artisan user:reset-password viewer@slss.edu.tt --password="YourStrongPassword789!"
```

**⚠️ Password Requirements:**

- Minimum 8 characters
- Mix of uppercase, lowercase, numbers recommended
- Special characters recommended
- DO NOT use simple passwords like "password123"

**✅ Save these passwords securely!** You'll need them to log in.

### Step 10: Test Application

#### 10.1 Check Application Status

```bash
# Check if there are any errors
tail -50 storage/logs/laravel.log
```

If no errors, you should see normal log entries or the file might be empty.

#### 10.2 Test in Browser

Open your browser and navigate to:

```
https://darkcyan-whale-509153.hostingersite.com
```

**Test Checklist:**

- [ ] Login page loads without errors
- [ ] Can log in with new admin credentials
- [ ] No console errors (press F12 to check)
- [ ] Students page loads
- [ ] Navigation works

#### 10.3 Test Rate Limiting (Optional)

To verify rate limiting is working:

1. **Test Login Rate Limit:**

   - Try to log in with wrong password 6 times quickly
   - After 5th attempt, you should see "Too Many Attempts" error
   - Wait 60 seconds and you can try again
2. **Test Security Headers:**

   - Press F12 in browser
   - Go to Network tab
   - Refresh page
   - Click on any request
   - Check Response Headers for:
     - `X-Frame-Options: SAMEORIGIN`
     - `X-Content-Type-Options: nosniff`
     - `Content-Security-Policy: ...`

### Step 11: Final Verification

```bash
# Check application is running
curl -I https://darkcyan-whale-509153.hostingersite.com 2>/dev/null | head -10
```

**Expected Output (should include):**

```
HTTP/2 200
x-frame-options: SAMEORIGIN
x-content-type-options: nosniff
x-xss-protection: 1; mode=block
referrer-policy: strict-origin-when-cross-origin
content-security-policy: ...
permissions-policy: ...
```

---

## ✅ Deployment Complete!

If all tests pass, your security enhancements are now live! 🎉

---

## 🔐 What Was Deployed?

### New Security Features

1. **Rate Limiting Protection**

   - Login: 5 attempts per minute
   - CSV Import: 10 attempts per minute
   - Prevents brute force attacks
2. **Security Headers Middleware**

   - X-Frame-Options (clickjacking protection)
   - X-Content-Type-Options (MIME-sniffing protection)
   - Content-Security-Policy (XSS protection)
   - X-XSS-Protection (legacy browser protection)
   - Referrer-Policy (privacy protection)
   - Permissions-Policy (browser API restrictions)
3. **Enhanced Password Management**

   - Environment variable support
   - Security warnings in seeder
   - Duplicate prevention
4. **Password Reset CLI Tool**

   - Command: `php artisan user:reset-password`
   - Interactive and non-interactive modes
   - Password validation

### Security Score Improvement

- **Before:** 95/100
- **After:** 98/100 ⬆️ (+3 points)

---

## 🆘 Troubleshooting

### Issue: Git Pull Conflicts

**Error:**

```
error: Your local changes to the following files would be overwritten by merge:
    .env
```

**Solution:**

```bash
# Backup your .env file
cp .env .env.backup

# Stash local changes
git stash

# Pull changes
git pull origin master

# Restore .env if needed
cp .env.backup .env
```

---

### Issue: Permission Errors

**Error:**

```
The stream or file "storage/logs/laravel.log" could not be opened: failed to open stream: Permission denied
```

**Solution:**

```bash
chmod -R 775 storage bootstrap/cache
```

---

### Issue: Cache Errors

**Error:**

```
file_put_contents(...): failed to open stream
```

**Solution:**

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Fix permissions
chmod -R 775 storage bootstrap/cache

# Regenerate caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

### Issue: "Too Many Redirects" Error

**Solution:**

```bash
# Clear all caches and sessions
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Clear browser cookies and try again
```

---

### Issue: Password Reset Command Not Found

**Error:**

```
Command "user:reset-password" is not defined.
```

**Solution:**

```bash
# Verify file exists
ls -la app/Console/Commands/ResetUserPassword.php

# Clear cache and try again
php artisan config:clear
php artisan cache:clear

# Try the command again
php artisan user:reset-password
```

---

## 📝 Post-Deployment Checklist

After successful deployment, complete these tasks:

- [ ] All default passwords changed
- [ ] Login tested with new credentials
- [ ] Security headers verified in browser
- [ ] Rate limiting tested (optional)
- [ ] Application logs checked for errors
- [ ] All main features tested (students, import, PDF)
- [ ] Backup strategy planned
- [ ] Team members notified of new passwords

---

## 🔄 Future Updates

To deploy future updates, follow the same process:

```bash
# 1. Connect to server
ssh -p 65002 u269010508@31.97.97.131

# 2. Navigate to app
cd ~/public_html/student_registrationV2/slss-laravel

# 3. Pull changes
git pull origin master

# 4. Update dependencies (if needed)
composer install --optimize-autoloader --no-dev

# 5. Run migrations (if any new ones)
php artisan migrate --force

# 6. Clear and regenerate caches
php artisan config:clear && php artisan cache:clear
php artisan config:cache && php artisan route:cache && php artisan view:cache

# 7. Test application
```

---

## 📞 Support Commands

### Check Application Status

```bash
# View logs
tail -100 storage/logs/laravel.log

# Check routes
php artisan route:list

# View users
php artisan tinker
>>> User::all(['name', 'email', 'role'])
>>> exit
```

### Database Operations

```bash
# Check database connection
php artisan db:show

# Run migrations
php artisan migrate:status
```

### View Configuration

```bash
# View current config
php artisan config:show

# View environment
php artisan env
```

---

**Deployment Guide Version:** 1.0
**Last Updated:** July 7, 2026
**Compatible With:** Laravel 11.54.0, PHP 8.2.30

---

🎉 **Congratulations!** Your application is now deployed with enhanced security features!
