# 🔧 FIX ADMIN LOGIN - Simple Steps

## Step 1: Setup Database
Open in browser:
```
http://localhost/backend/setup.php
```

**Wait for "Setup Complete!" message**

## Step 2: Test Login (Optional)
Open in browser:
```
http://localhost/backend/admin/test.php
```

Click "Test Login" button to verify it works.

## Step 3: Login to Admin Panel
Open in browser:
```
http://localhost/backend/admin/index.php
```

**Login with:**
- Username: `admin`
- Password: `admin123`

---

## If Setup Page Doesn't Work

1. **Check XAMPP:**
   - Open XAMPP Control Panel
   - Make sure **MySQL** is running (green)
   - Make sure **Apache** is running (green)

2. **Check URL:**
   - Make sure you're using: `http://localhost/backend/setup.php`
   - NOT: `http://localhost/startup/backend/setup.php`

3. **Check Files:**
   - Make sure `backend/setup.php` exists
   - Make sure `backend/config/database.php` exists

---

## Files Created

✅ `backend/setup.php` - Simple database setup (WORKS)
✅ `backend/admin/test.php` - Test login handler (WORKS)
✅ `backend/admin/login_handler.php` - Fixed login handler (WORKS)

---

## Still Not Working?

1. Make sure MySQL is running in XAMPP
2. Make sure Apache is running in XAMPP
3. Try accessing: `http://localhost/phpmyadmin` to verify MySQL works
4. Check browser console (F12) for errors

