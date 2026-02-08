# Web Server Setup Guide

## Error: ERR_CONNECTION_REFUSED

This error means your web server (Apache/Nginx) is not running. Follow these steps to fix it.

## Option 1: Using XAMPP (Recommended for Windows)

### Step 1: Install XAMPP
1. Download XAMPP from: https://www.apachefriends.org/
2. Install it (usually to `C:\xampp`)

### Step 2: Start Apache
1. Open **XAMPP Control Panel**
2. Click **Start** button next to **Apache**
3. Wait until it shows "Running" (green)

### Step 3: Access Admin Panel
- **Admin Login:** `http://localhost/backend/admin/index.php`
- Or: `http://localhost/backend/admin/`

### Step 4: Place Your Files
- Copy your `backend` folder to: `C:\xampp\htdocs\backend`
- Or move your entire project to: `C:\xampp\htdocs\`

## Option 2: Using WAMP

### Step 1: Install WAMP
1. Download WAMP from: https://www.wampserver.com/
2. Install it

### Step 2: Start WAMP
1. Open **WAMP Server**
2. Click the WAMP icon in system tray
3. Click **Start All Services**
4. Wait until icon turns green

### Step 3: Access Admin Panel
- **Admin Login:** `http://localhost/backend/admin/index.php`

### Step 4: Place Your Files
- Copy `backend` folder to: `C:\wamp64\www\backend`

## Option 3: Using Laragon (Lightweight)

### Step 1: Install Laragon
1. Download Laragon from: https://laragon.org/
2. Install it

### Step 2: Start Laragon
1. Open **Laragon**
2. Click **Start All** button
3. Wait until services are running

### Step 3: Access Admin Panel
- **Admin Login:** `http://localhost/backend/admin/index.php`

### Step 4: Place Your Files
- Copy `backend` folder to: `C:\laragon\www\backend`

## Option 4: Using PHP Built-in Server (Quick Test)

### Step 1: Open Command Prompt
1. Press `Win + R`
2. Type `cmd` and press Enter
3. Navigate to your project:
   ```bash
   cd C:\Users\km450\Downloads\startup\backend
   ```

### Step 2: Start PHP Server
```bash
php -S localhost:8000
```

### Step 3: Access Admin Panel
- **Admin Login:** `http://localhost:8000/admin/index.php`

## Quick Troubleshooting

### Check if Apache is Running
1. Open Task Manager (`Ctrl + Shift + Esc`)
2. Look for `httpd.exe` or `apache.exe` process
3. If not found, start your web server

### Check Port 80
1. Open Command Prompt as Administrator
2. Run: `netstat -ano | findstr :80`
3. If port 80 is in use, either:
   - Stop the conflicting service
   - Or use a different port (e.g., 8080)

### Verify PHP Installation
1. Open Command Prompt
2. Run: `php -v`
3. If error, install PHP or use XAMPP/WAMP

### Check File Paths
- Make sure `backend` folder is in the web server's document root:
  - XAMPP: `C:\xampp\htdocs\backend`
  - WAMP: `C:\wamp64\www\backend`
  - Laragon: `C:\laragon\www\backend`

## Common Issues

### Issue: "Apache won't start"
**Solution:**
- Check if port 80 is already in use
- Run XAMPP/WAMP as Administrator
- Check error logs in web server control panel

### Issue: "PHP not found"
**Solution:**
- Install XAMPP/WAMP which includes PHP
- Or add PHP to system PATH

### Issue: "Database connection failed"
**Solution:**
- Start MySQL in XAMPP/WAMP control panel
- Check database credentials in `config/database.php`

## Recommended Setup for Windows

**Best Option: XAMPP**
- Includes Apache, PHP, MySQL
- Easy to use
- Free and reliable
- Download: https://www.apachefriends.org/

## After Server is Running

1. **Create Admin User:**
   ```bash
   php backend/database/create_admin.php
   ```

2. **Access Admin Panel:**
   - URL: `http://localhost/backend/admin/index.php`
   - Username: `admin`
   - Password: `admin123`

3. **Test API:**
   - `http://localhost/backend/api/products.php`
   - `http://localhost/backend/api/categories.php`


