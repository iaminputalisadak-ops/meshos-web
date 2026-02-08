# Admin Panel Credentials

## Admin Panel URL

**Admin Login Page:** `http://localhost/backend/admin/index.php`  
**Admin Dashboard:** `http://localhost/backend/admin/dashboard.php`

## Default Admin Login

**Username:** `admin`  
**Password:** `admin123`  
**Email:** `admin@meesho.com`  
**Role:** Super Admin

## Important Security Notes

⚠️ **CHANGE THE DEFAULT PASSWORD IMMEDIATELY AFTER FIRST LOGIN!**

The default credentials are for initial setup only. For production, you must:
1. Change the default password
2. Use strong, unique passwords
3. Enable HTTPS/SSL
4. Implement additional security measures

## Creating/Resetting Admin User

To create or reset the admin user, run:

```bash
php backend/database/create_admin.php
```

This will create/update the admin user with the default credentials.

## Admin API Endpoints

### Login
**POST** `/api/admin/login.php`
```json
{
  "username": "admin",
  "password": "admin123"
}
```

### Check Authentication
**GET** `/api/admin/auth.php`
- Returns admin info if logged in
- Returns 401 if not authenticated

### Logout
**GET** `/api/admin/login.php?logout=1`

### Manage Products (Admin Only)
- **GET** `/api/admin/products.php` - List all products
- **POST** `/api/admin/products.php` - Create product
- **PUT** `/api/admin/products.php` - Update product
- **DELETE** `/api/admin/products.php?id=1` - Delete product

## Session Management

The admin panel uses PHP sessions for authentication. Make sure:
- Sessions are enabled in PHP
- Session cookies are secure (in production)
- Session timeout is configured appropriately

## Changing Admin Password

To change the admin password, you can:

1. **Via Database:**
   ```sql
   UPDATE admin_users 
   SET password = '$2y$10$...' 
   WHERE username = 'admin';
   ```
   (Use `password_hash()` in PHP to generate the hash)

2. **Via Script:**
   Edit `backend/database/create_admin.php` and change the password, then run it.

3. **Via Admin Panel:**
   (If you implement a password change feature in the admin panel)

## Troubleshooting

### Can't Login
- Verify admin user exists in database
- Check password hash is correct
- Ensure sessions are working
- Check PHP error logs

### Session Issues
- Clear browser cookies
- Check PHP session configuration
- Verify session storage directory is writable

### Database Connection
- Verify database credentials in `config/database.php`
- Ensure MySQL service is running
- Check admin_users table exists

