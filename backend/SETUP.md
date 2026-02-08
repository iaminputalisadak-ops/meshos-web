# Backend Setup Guide

## Quick Start

### Step 1: Database Setup

1. **Create MySQL Database:**
   ```sql
   CREATE DATABASE meesho_ecommerce;
   ```

2. **Import Schema:**
   ```bash
   mysql -u root -p meesho_ecommerce < database/schema.sql
   ```

   Or use phpMyAdmin:
   - Open phpMyAdmin
   - Select `meesho_ecommerce` database
   - Go to Import tab
   - Choose `database/schema.sql` file
   - Click Go

### Step 2: Configure Database

Edit `config/database.php` and update:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'meesho_ecommerce');
```

### Step 3: Seed Sample Data (Optional)

Run the seeding script to add sample products:
```bash
php database/seed_data.php
```

### Step 4: Test API

1. **Start your web server** (Apache/Nginx)

2. **Test endpoints:**
   - Products: `http://localhost/backend/api/products.php`
   - Categories: `http://localhost/backend/api/categories.php`
   - API Info: `http://localhost/backend/api/index.php`

## Directory Structure

```
backend/
├── api/                    # API endpoints
│   ├── products.php       # Products API
│   ├── categories.php     # Categories API
│   ├── cart.php          # Cart API
│   ├── subscriptions.php # Subscriptions API
│   └── index.php         # API documentation
├── config/                 # Configuration files
│   ├── database.php      # Database connection
│   ├── cors.php         # CORS settings
│   ├── error_handler.php # Error handling
│   └── config.php       # Main config
├── database/              # Database files
│   ├── schema.sql       # Database schema
│   └── seed_data.php    # Sample data seeder
├── .htaccess            # Apache configuration
├── README.md            # Full documentation
└── SETUP.md            # This file
```

## Common Issues

### Issue: "Connection failed"
**Solution:** Check database credentials in `config/database.php` and ensure MySQL is running.

### Issue: "404 Not Found"
**Solution:** 
- Verify `.htaccess` is enabled in Apache
- Check file paths are correct
- Ensure mod_rewrite is enabled

### Issue: CORS Errors
**Solution:** Update `config/cors.php` with your frontend URL:
```php
header('Access-Control-Allow-Origin: http://localhost:3000');
```

### Issue: "Call to undefined function"
**Solution:** Ensure MySQLi extension is enabled in PHP:
```bash
php -m | grep mysqli
```

## Next Steps

1. Update CORS settings for your frontend
2. Configure production database credentials
3. Set up SSL/HTTPS for production
4. Implement authentication if needed
5. Add more API endpoints as required

## Support

For detailed API documentation, see `README.md`


