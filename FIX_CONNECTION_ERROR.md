# 🔧 Fix: ERR_CONNECTION_REFUSED

## Problem
You're getting `ERR_CONNECTION_REFUSED` when accessing:
- `http://localhost/backend/admin/index.php`
- `http://localhost/backend/api/products.php`

## Solution

### Step 1: Move Backend to XAMPP htdocs

**The backend folder MUST be in XAMPP's htdocs directory!**

**Option A: Use the Script (Easiest)**
1. Double-click: `backend/MOVE_TO_XAMPP.bat`
2. It will copy your backend to `C:\xampp\htdocs\backend`
3. Follow the prompts

**Option B: Manual Copy**
1. Open File Explorer
2. Go to: `C:\xampp\htdocs`
3. Copy your `backend` folder from:
   - `C:\Users\km450\Downloads\startup\backend`
4. Paste it into: `C:\xampp\htdocs\backend`

### Step 2: Verify Apache is Running

1. Open **XAMPP Control Panel**
2. Check **Apache** - should show "Running" (green)
3. If not running, click **"Start"** button

### Step 3: Test Setup

**Run the checker:**
1. Double-click: `backend/CHECK_XAMPP_SETUP.bat`
2. It will verify everything is correct

**Or test manually:**
- Open: `http://localhost` (should show XAMPP dashboard)
- Open: `http://localhost/backend/api/products.php` (should show JSON)
- Open: `http://localhost/backend/admin/index.php` (should show login page)

---

## Quick Fix Commands

### Check Setup
```batch
backend\CHECK_XAMPP_SETUP.bat
```

### Move Backend to XAMPP
```batch
backend\MOVE_TO_XAMPP.bat
```

---

## Common Issues

### Issue 1: "Backend folder not found"
**Solution:** Run `MOVE_TO_XAMPP.bat` to copy backend to XAMPP

### Issue 2: "Apache not running"
**Solution:** 
1. Open XAMPP Control Panel
2. Click "Start" next to Apache
3. Wait for it to turn green

### Issue 3: "Port 80 already in use"
**Solution:**
1. Open XAMPP Control Panel
2. Click "Config" next to Apache
3. Select "httpd.conf"
4. Change `Listen 80` to `Listen 8080`
5. Save and restart Apache
6. Use: `http://localhost:8080/backend/...`

### Issue 4: "Permission denied"
**Solution:**
1. Right-click `MOVE_TO_XAMPP.bat`
2. Select "Run as Administrator"

---

## Correct File Structure

```
C:\xampp\
└── htdocs\
    └── backend\          ← Backend MUST be here!
        ├── admin\
        ├── api\
        ├── config\
        ├── database\
        └── ...
```

**NOT:**
```
C:\Users\km450\Downloads\startup\backend\  ← Wrong location!
```

---

## After Fixing

1. **Setup Database:**
   - Open: `http://localhost/backend/database/setup_database.php`

2. **Access Admin Panel:**
   - Open: `http://localhost/backend/admin/index.php`
   - Login: `admin` / `admin123`

3. **Test API:**
   - Open: `http://localhost/backend/api/products.php`

---

## Still Not Working?

1. **Check XAMPP Control Panel:**
   - Apache status (should be green)
   - MySQL status (should be green)

2. **Check Windows Firewall:**
   - May be blocking Apache
   - Allow Apache through firewall

3. **Check Port 80:**
   - Open: `http://localhost`
   - Should show XAMPP dashboard
   - If not, Apache is not running

4. **Check Error Logs:**
   - XAMPP Control Panel → Apache → Logs
   - Look for errors

---

## ✅ Success Checklist

- [ ] Backend folder is in `C:\xampp\htdocs\backend`
- [ ] Apache is running (green in XAMPP)
- [ ] MySQL is running (green in XAMPP)
- [ ] `http://localhost` shows XAMPP dashboard
- [ ] `http://localhost/backend/api/products.php` shows JSON
- [ ] `http://localhost/backend/admin/index.php` shows login page

---

## Need Help?

Run the checker script:
```batch
backend\CHECK_XAMPP_SETUP.bat
```

It will tell you exactly what's wrong!

