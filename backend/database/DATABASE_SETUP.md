# Database setup (meesho_ecommerce)

The app uses **MySQL** with database name **`meesho_ecommerce`**.  
Config: `backend/config/database.php` (default: host `localhost`, user `root`, no password).

## Option 1: Run the SQL file (recommended)

**Using phpMyAdmin (XAMPP):**
1. Start Apache + MySQL in XAMPP.
2. Open http://localhost/phpmyadmin
3. Click **Import**, choose file: `backend/database/schema.sql`
4. Click **Go**. This creates the database, all tables, sample categories, and default admin.

**Using MySQL command line** (if `mysql` is in PATH):
```bash
mysql -u root -p < backend/database/schema.sql
```
(Leave password blank for default XAMPP, or add `-p` and type your MySQL password.)

## Option 2: PHP in browser (XAMPP)

1. Copy the project so it’s under the web root (e.g. `htdocs/Shopping` or `htdocs/meshos-web`).
2. Start Apache + MySQL in XAMPP.
3. Open in browser:  
   **http://localhost/Shopping/backend/database/setup_database.php**  
   (adjust path if your folder name is different.)
4. The page creates the database, tables, categories, and admin user.

## Option 3: Run schema via PHP CLI

From the **project root** (when PHP is in PATH, e.g. XAMPP shell):
```bash
php backend/database/run_schema.php
```
Or in browser:  
**http://localhost/YourProjectFolder/backend/database/run_schema.php**

---

## After setup

- **Database:** `meesho_ecommerce`
- **Tables:** categories, products, product_images, users, cart, subscriptions, orders, order_items, brands, admin_users
- **Default admin:** username `admin`, password `admin123`, email `admin@meesho.com`  
  If you only imported `schema.sql`, open **setup_database.php** once in the browser so the admin password is set to `admin123`.

Optional: add sample products by running (from project root):
```bash
php backend/database/seed_data.php
```
