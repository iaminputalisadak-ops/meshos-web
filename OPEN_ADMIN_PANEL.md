# 🔐 Open Admin Panel

## Quick Access

### Admin Panel Login URL:
```
http://localhost/backend/admin/index.php
```

---

## Steps to Open

### Step 1: Make Sure XAMPP is Running
1. Open **XAMPP Control Panel**
2. Check that **Apache** is running (should be GREEN)
3. Check that **MySQL** is running (should be GREEN)
4. If not running, click **Start** for both

### Step 2: Open Admin Panel
1. Open your web browser (Chrome, Firefox, Edge, etc.)
2. Go to: `http://localhost/backend/admin/index.php`
3. You should see the login page

### Step 3: Login
**Default Credentials:**
- **Username:** `admin`
- **Password:** `admin123`

---

## If Admin Panel Doesn't Open

### Error: "Connection Refused" or "This site can't be reached"
**Solution:**
1. Make sure **Apache** is running in XAMPP
2. Check if Apache is using port 80 (default)
3. Try: `http://127.0.0.1/backend/admin/index.php`

### Error: "Database not set up"
**Solution:**
1. Run database setup first: `http://localhost/backend/setup.php`
2. Wait for "Setup Complete!" message
3. Then try admin panel again

### Error: "404 Not Found"
**Solution:**
1. Make sure files are in: `C:\xampp\htdocs\backend\admin\`
2. Or check your XAMPP installation path
3. Verify the file `index.php` exists in the admin folder

---

## Admin Panel URLs

- **Login:** `http://localhost/backend/admin/index.php`
- **Dashboard:** `http://localhost/backend/admin/dashboard.php` (after login)
- **Database Setup:** `http://localhost/backend/setup.php`

---

## Quick Test

1. **Test Apache:** `http://localhost` (should show XAMPP welcome page)
2. **Test MySQL:** `http://localhost/phpmyadmin` (should open phpMyAdmin)
3. **Test Admin:** `http://localhost/backend/admin/index.php` (should show login page)

---

## After Login

Once you login successfully, you'll be redirected to:
- **Dashboard:** `http://localhost/backend/admin/dashboard.php`

The dashboard shows:
- Total Products
- Total Categories
- Total Orders
- Total Users
- Recent Products table
- Recent Orders table
- Database Tables section

---

**Need Help?** Check `FIX_NOW.md` for troubleshooting steps.

