# Meesho E-commerce Backend API

PHP and MySQL backend for the Meesho e-commerce platform.

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher (or MariaDB 10.2+)
- Apache/Nginx web server with mod_rewrite enabled
- MySQLi extension enabled in PHP

## Installation

### 1. Database Setup

1. Create a MySQL database:
```sql
CREATE DATABASE meesho_ecommerce;
```

2. Import the database schema:
```bash
mysql -u root -p meesho_ecommerce < database/schema.sql
```

Or use phpMyAdmin to import the `database/schema.sql` file.

### 2. Configuration

1. Update database credentials in `config/database.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
define('DB_NAME', 'meesho_ecommerce');
```

2. Update CORS settings in `config/cors.php` if your frontend runs on a different port:
```php
header('Access-Control-Allow-Origin: http://localhost:3000');
```

### 3. Web Server Setup

#### Apache Setup

1. Place the `backend` folder in your web server directory (e.g., `htdocs`, `www`, or `/var/www/html`)

2. Ensure `.htaccess` is enabled in Apache:
```apache
AllowOverride All
```

3. Access the API:
```
http://localhost/backend/api/products.php
```

#### Nginx Setup

Add this configuration to your Nginx server block:
```nginx
location /backend {
    root /var/www/html;
    index index.php;
    
    try_files $uri $uri/ /backend/api/$uri;
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    }
}
```

## API Endpoints

### Products

- **GET** `/api/products.php` - Get all products
  - Query params: `category`, `category_id`, `id`, `search`, `limit`, `offset`
  - Example: `/api/products.php?category=lingerie&limit=10`

- **GET** `/api/products.php?id=32` - Get single product by ID

### Categories

- **GET** `/api/categories.php` - Get all categories

### Cart

- **GET** `/api/cart.php` - Get cart items
  - Query params: `user_id` (optional)
  
- **POST** `/api/cart.php` - Add item to cart
  ```json
  {
    "product_id": 32,
    "quantity": 2,
    "user_id": 1
  }
  ```

- **PUT** `/api/cart.php` - Update cart item
  ```json
  {
    "cart_id": 1,
    "quantity": 3
  }
  ```

- **DELETE** `/api/cart.php?id=1` - Remove item from cart

### Subscriptions

- **GET** `/api/subscriptions.php?user_id=1` - Get user subscription

- **POST** `/api/subscriptions.php` - Create subscription
  ```json
  {
    "user_id": 1,
    "plan_type": "Premium",
    "price": 299,
    "discount_percentage": 10
  }
  ```

- **PUT** `/api/subscriptions.php` - Update subscription (cancel)
  ```json
  {
    "subscription_id": 1,
    "status": "cancelled"
  }
  ```

## Database Schema

The database includes the following tables:

- `categories` - Product categories
- `products` - Product information
- `product_images` - Product image URLs
- `users` - User accounts
- `cart` - Shopping cart items
- `subscriptions` - User subscriptions
- `orders` - Order information
- `order_items` - Order line items
- `brands` - Brand information

## Security Notes

1. **Production Settings**: Update `config/error_handler.php` to disable error display:
   ```php
   ini_set('display_errors', 0);
   ```

2. **Database Credentials**: Never commit database credentials to version control. Use environment variables or a separate config file.

3. **Input Validation**: Always validate and sanitize user input before database queries.

4. **SQL Injection**: All queries use prepared statements to prevent SQL injection.

5. **CORS**: Update CORS settings to match your production domain.

## Testing

Test the API endpoints using:

- **cURL**:
  ```bash
  curl http://localhost/backend/api/products.php
  ```

- **Postman**: Import the API endpoints and test each one

- **Browser**: Visit `http://localhost/backend/api/products.php` to see JSON response

## Frontend Integration

Update your React frontend to use these API endpoints:

```javascript
// Example: Fetch products
fetch('http://localhost/backend/api/products.php?category=lingerie')
  .then(res => res.json())
  .then(data => console.log(data));
```

## Troubleshooting

1. **500 Internal Server Error**: Check PHP error logs and database connection
2. **CORS Issues**: Verify CORS settings match your frontend URL
3. **Database Connection Failed**: Verify database credentials and MySQL service is running
4. **404 Not Found**: Check web server configuration and file paths

## Support

For issues or questions, check:
- PHP error logs
- MySQL error logs
- Web server error logs
- Browser console for CORS errors


