# Quick Start - Admin Panel Setup

## ❌ Error: Connection Refused

Your web server is not running. Follow these steps:

## 🚀 Quick Solution (Choose One)

### Option 1: Install XAMPP (Easiest - Recommended)

1. **Download XAMPP:**
   - Visit: https://www.apachefriends.org/
   - Download XAMPP for Windows
   - Install it (default location: `C:\xampp`)

2. **Start Apache:**
   - Open **XAMPP Control Panel**
   - Click **Start** next to **Apache**
   - Wait for it to turn green

3. **Move Your Files:**
   - Copy your `backend` folder to: `C:\xampp\htdocs\backend`
   - Or move entire project to: `C:\xampp\htdocs\startup`

4. **Access Admin Panel:**
   - Open browser: `http://localhost/backend/admin/index.php`
   - Login: `admin` / `admin123`

### Option 2: Use PHP Built-in Server (If PHP is installed)

1. **Open Command Prompt** in your project folder

2. **Navigate to backend folder:**
   ```bash
   cd backend
   ```

3. **Start PHP Server:**
   ```bash
   php -S localhost:8000
   ```

4. **Access Admin Panel:**
   - Open browser: `http://localhost:8000/admin/index.php`
   - Login: `admin` / `admin123`

### Option 3: Use the Batch Script

1. **Double-click:** `backend/start_server.bat`
2. **If PHP is installed:** Server will start automatically
3. **If not:** Follow instructions to install XAMPP

## 📋 Step-by-Step Setup

### Step 1: Install Web Server

**Download XAMPP:**
- URL: https://www.apachefriends.org/download.html
- Choose: **XAMPP for Windows**
- Version: Latest (PHP 8.x)

### Step 2: Install XAMPP

1. Run the installer
2. Select components: **Apache**, **MySQL**, **PHP**
3. Install to: `C:\xampp` (default)
4. Complete installation

### Step 3: Start Services

1. Open **XAMPP Control Panel**
2. Click **Start** for:
   - ✅ **Apache** (Web Server)
   - ✅ **MySQL** (Database)

### Step 4: Setup Project

1. **Copy backend folder:**
   - From: `C:\Users\km450\Downloads\startup\backend`
   - To: `C:\xampp\htdocs\backend`

2. **Or move entire project:**
   - From: `C:\Users\km450\Downloads\startup`
   - To: `C:\xampp\htdocs\startup`

### Step 5: Setup Database

1. **Open phpMyAdmin:**
   - URL: `http://localhost/phpmyadmin`

2. **Create Database:**
   - Click "New" in left sidebar
   - Database name: `meesho_ecommerce`
   - Click "Create"

3. **Import Schema:**
   - Select `meesho_ecommerce` database
   - Click "Import" tab
   - Choose file: `backend/database/schema.sql`
   - Click "Go"

4. **Create Admin User:**
   - Open Command Prompt
   - Navigate to: `C:\xampp\htdocs\backend\database`
   - Run: `php create_admin.php`

### Step 6: Access Admin Panel

1. **Open Browser:**
   - URL: `http://localhost/backend/admin/index.php`

2. **Login:**
   - Username: `admin`
   - Password: `admin123`

## ✅ Verify Setup

### Test 1: Check Apache
- Open: `http://localhost`
- Should see XAMPP welcome page

### Test 2: Check PHP
- Open: `http://localhost/backend/api/index.php`
- Should see API documentation

### Test 3: Check Database
- Open: `http://localhost/phpmyadmin`
- Should see `meesho_ecommerce` database

### Test 4: Check Admin Panel
- Open: `http://localhost/backend/admin/index.php`
- Should see login page

## 🔧 Troubleshooting

### Problem: Apache won't start
**Solution:**
- Port 80 might be in use
- Run XAMPP as Administrator
- Check if Skype/other apps use port 80
- Change Apache port in XAMPP settings

### Problem: MySQL won't start
**Solution:**
- Port 3306 might be in use
- Run XAMPP as Administrator
- Check MySQL service in Services

### Problem: "Access Denied" in phpMyAdmin
**Solution:**
- Default MySQL user: `root`
- Default password: (empty/blank)
- Leave password field empty

### Problem: Admin panel shows blank page
**Solution:**
- Check PHP error logs: `C:\xampp\apache\logs\error.log`
- Enable error display in `php.ini`
- Check file permissions

## 📞 Need Help?

1. Check `backend/SERVER_SETUP.md` for detailed guide
2. Check XAMPP documentation
3. Verify all files are in correct location

## 🎯 Quick Checklist

- [ ] XAMPP installed
- [ ] Apache running (green in XAMPP)
- [ ] MySQL running (green in XAMPP)
- [ ] Backend folder in `C:\xampp\htdocs\backend`
- [ ] Database created and schema imported
- [ ] Admin user created
- [ ] Can access `http://localhost/backend/admin/index.php`


