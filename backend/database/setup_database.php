<?php
/**
 * Database Setup Script
 * Run this once to create database and tables
 * Usage: Open in browser: http://localhost/backend/database/setup_database.php
 * Or run: php setup_database.php
 */

require_once '../config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - Meesho E-commerce</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .success { color: #4CAF50; padding: 10px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; }
        .error { color: #f44336; padding: 10px; background: #ffebee; border-radius: 5px; margin: 10px 0; }
        .info { color: #2196F3; padding: 10px; background: #e3f2fd; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class='box'>
        <h1>🗄️ Database Setup</h1>";

try {
    // Connect to MySQL (without database)
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "<div class='success'>✅ Connected to MySQL successfully!</div>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='success'>✅ Database '" . DB_NAME . "' created successfully!</div>";
    } else {
        echo "<div class='info'>ℹ️ Database already exists or: " . $conn->error . "</div>";
    }
    
    // Select database
    $conn->select_db(DB_NAME);
    
    // Create tables directly (more reliable than parsing SQL file)
    echo "<h2>📊 Creating Database Tables...</h2>";
    
    $tables = [
        'categories' => "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            slug VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'products' => "CREATE TABLE IF NOT EXISTS products (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(255) NOT NULL,
            category_id INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            original_price DECIMAL(10, 2) NOT NULL,
            discount INT DEFAULT 0,
            image TEXT,
            description TEXT,
            rating DECIMAL(3, 1) DEFAULT 0.0,
            reviews INT DEFAULT 0,
            in_stock BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_category (category_id),
            INDEX idx_name (name),
            INDEX idx_price (price)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'product_images' => "CREATE TABLE IF NOT EXISTS product_images (
            id INT AUTO_INCREMENT PRIMARY KEY,
            product_id INT NOT NULL,
            image_url TEXT NOT NULL,
            is_primary BOOLEAN DEFAULT FALSE,
            display_order INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'users' => "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'cart' => "CREATE TABLE IF NOT EXISTS cart (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            session_id VARCHAR(255),
            product_id INT NOT NULL,
            quantity INT NOT NULL DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_session (session_id),
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'subscriptions' => "CREATE TABLE IF NOT EXISTS subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            plan_type VARCHAR(50) NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            discount_percentage INT DEFAULT 0,
            status ENUM('active', 'cancelled', 'expired') DEFAULT 'active',
            start_date DATE NOT NULL,
            end_date DATE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'orders' => "CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            session_id VARCHAR(255),
            total_amount DECIMAL(10, 2) NOT NULL,
            discount_amount DECIMAL(10, 2) DEFAULT 0,
            final_amount DECIMAL(10, 2) NOT NULL,
            status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
            shipping_address TEXT,
            payment_method VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_user (user_id),
            INDEX idx_session (session_id),
            INDEX idx_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'order_items' => "CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT NOT NULL,
            price DECIMAL(10, 2) NOT NULL,
            discount DECIMAL(10, 2) DEFAULT 0,
            total DECIMAL(10, 2) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_order (order_id),
            INDEX idx_product (product_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'brands' => "CREATE TABLE IF NOT EXISTS brands (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            logo_url TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
        
        'admin_users' => "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
            status ENUM('active', 'inactive') DEFAULT 'active',
            last_login TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];
    
    $successCount = 0;
    $errorCount = 0;
    $skippedCount = 0;
    
    // Create tables in order (respecting foreign key dependencies)
    $tableOrder = ['categories', 'products', 'product_images', 'users', 'cart', 'subscriptions', 'orders', 'order_items', 'brands', 'admin_users'];
    
    foreach ($tableOrder as $tableName) {
        if (!isset($tables[$tableName])) {
            continue;
        }
        
        $createSQL = $tables[$tableName];
        
        if ($conn->query($createSQL) === TRUE) {
            // Check if table was actually created
            $check = $conn->query("SHOW TABLES LIKE '$tableName'");
            if ($check && $check->num_rows > 0) {
                $successCount++;
                echo "<div class='success'>✅ Table '$tableName' created successfully!</div>";
            } else {
                $skippedCount++;
                echo "<div class='info'>ℹ️ Table '$tableName' may already exist</div>";
            }
        } else {
            $error = $conn->error;
            if (strpos($error, 'already exists') !== false || 
                strpos($error, 'Duplicate') !== false ||
                (strpos($error, 'Table') !== false && strpos($error, 'already exists') !== false)) {
                $skippedCount++;
                echo "<div class='info'>ℹ️ Table '$tableName' already exists</div>";
            } else {
                $errorCount++;
                echo "<div class='error'>❌ Error creating table '$tableName': " . htmlspecialchars($error) . "</div>";
            }
        }
    }
    
    // Add foreign keys after all tables are created
    echo "<h3>🔗 Adding Foreign Keys...</h3>";
    
    $foreignKeys = [
        "ALTER TABLE products ADD CONSTRAINT fk_products_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE",
        "ALTER TABLE product_images ADD CONSTRAINT fk_product_images_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE",
        "ALTER TABLE cart ADD CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE cart ADD CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE",
        "ALTER TABLE subscriptions ADD CONSTRAINT fk_subscriptions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE",
        "ALTER TABLE orders ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL",
        "ALTER TABLE order_items ADD CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE",
        "ALTER TABLE order_items ADD CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE"
    ];
    
    foreach ($foreignKeys as $fkSQL) {
        $conn->query($fkSQL); // Ignore errors if FK already exists
    }
    
    echo "<div class='success'>✅ Foreign keys added (if not already present)</div>";
    
    // Insert default categories
    echo "<h3>📝 Inserting Default Data...</h3>";
    
    $categories = [
        ['Popular', 'popular'],
        ['Women Western', 'women-western'],
        ['Lingerie', 'lingerie'],
        ['Men', 'men'],
        ['Kids & Toys', 'kids-toys'],
        ['Home & Kitchen', 'home-kitchen'],
        ['Beauty & Health', 'beauty-health'],
        ['Jewellery & Accessories', 'jewellery-accessories'],
        ['Bags & Footwear', 'bags-footwear'],
        ['Electronics', 'electronics'],
        ['Kurti, Saree & Lehenga', 'kurti-saree-lehenga']
    ];
    
    $catInserted = 0;
    foreach ($categories as $cat) {
        $insert = "INSERT IGNORE INTO categories (name, slug) VALUES ('" . $conn->real_escape_string($cat[0]) . "', '" . $conn->real_escape_string($cat[1]) . "')";
        if ($conn->query($insert)) {
            $catInserted++;
        }
    }
    echo "<div class='success'>✅ Inserted $catInserted categories</div>";
    
    echo "<div class='success'>✅ Created $successCount tables successfully!</div>";
    if ($skippedCount > 0) {
        echo "<div class='info'>ℹ️ Skipped $skippedCount tables (already exist - this is normal)</div>";
    }
    if ($errorCount > 0) {
        echo "<div class='error'>❌ $errorCount tables failed (see errors above)</div>";
    }
    
    // Create admin user
    echo "<h2>👤 Creating Admin User...</h2>";
    
    // First, check if admin_users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($tableCheck->num_rows === 0) {
        echo "<div class='info'>ℹ️ admin_users table does not exist. Creating it now...</div>";
        
        // Create admin_users table if it doesn't exist
        $createTableSQL = "CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            role VARCHAR(50) DEFAULT 'admin',
            status VARCHAR(20) DEFAULT 'active',
            last_login DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_username (username),
            INDEX idx_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($createTableSQL) === TRUE) {
            echo "<div class='success'>✅ admin_users table created successfully!</div>";
        } else {
            echo "<div class='error'>❌ Failed to create admin_users table: " . $conn->error . "</div>";
            throw new Exception("Failed to create admin_users table: " . $conn->error);
        }
    } else {
        echo "<div class='success'>✅ admin_users table already exists!</div>";
    }
    
    $adminUsername = 'admin';
    $adminPassword = 'admin123';
    $adminEmail = 'admin@meesho.com';
    $adminFullName = 'Administrator';
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    // Verify table structure before querying
    $verifyTable = $conn->query("DESCRIBE admin_users");
    if (!$verifyTable) {
        throw new Exception("Cannot verify admin_users table structure: " . $conn->error);
    }
    
    // Check if admin exists using a safer method
    $checkQuery = "SELECT id FROM admin_users WHERE username = '" . $conn->real_escape_string($adminUsername) . "'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult === false) {
        throw new Exception("Failed to check admin user: " . $conn->error);
    }
    
    if ($checkResult->num_rows > 0) {
        // Update existing admin
        $updateQuery = "UPDATE admin_users 
            SET password = '" . $conn->real_escape_string($hashedPassword) . "', 
                email = '" . $conn->real_escape_string($adminEmail) . "', 
                full_name = '" . $conn->real_escape_string($adminFullName) . "', 
                status = 'active'
            WHERE username = '" . $conn->real_escape_string($adminUsername) . "'";
        
        if ($conn->query($updateQuery) === TRUE) {
            echo "<div class='success'>✅ Admin user updated successfully!</div>";
        } else {
            echo "<div class='error'>❌ Failed to update admin user: " . $conn->error . "</div>";
        }
    } else {
        // Create new admin using INSERT
        $insertQuery = "INSERT INTO admin_users (username, email, password, full_name, role, status)
            VALUES (
                '" . $conn->real_escape_string($adminUsername) . "',
                '" . $conn->real_escape_string($adminEmail) . "',
                '" . $conn->real_escape_string($hashedPassword) . "',
                '" . $conn->real_escape_string($adminFullName) . "',
                'super_admin',
                'active'
            )";
        
        if ($conn->query($insertQuery) === TRUE) {
            echo "<div class='success'>✅ Admin user created successfully!</div>";
        } else {
            echo "<div class='error'>❌ Failed to create admin user: " . $conn->error . "</div>";
            throw new Exception("Failed to create admin user: " . $conn->error);
        }
    }
    
    echo "<div class='info'>
        <h3>📋 Admin Credentials:</h3>
        <p><strong>Username:</strong> <code>$adminUsername</code></p>
        <p><strong>Password:</strong> <code>$adminPassword</code></p>
        <p><strong>Email:</strong> <code>$adminEmail</code></p>
    </div>";
    
    // Verify tables
    echo "<h2>📊 Database Tables:</h2>";
    $tables = $conn->query("SHOW TABLES");
    $tableCount = 0;
    echo "<ul>";
    while ($row = $tables->fetch_array()) {
        echo "<li>✅ " . $row[0] . "</li>";
        $tableCount++;
    }
    echo "</ul>";
    echo "<div class='success'>✅ Total tables: $tableCount</div>";
    
    echo "<div class='info'>
        <h3>🎉 Setup Complete!</h3>
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Access Admin Panel: <a href='../admin/index.php' target='_blank'>http://localhost/backend/admin/index.php</a></li>
            <li>Login with: <code>admin</code> / <code>admin123</code></li>
            <li>Test API: <a href='../api/products.php' target='_blank'>http://localhost/backend/api/products.php</a></li>
        </ol>
    </div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    echo "<div class='info'>
        <h3>🔧 Troubleshooting:</h3>
        <ul>
            <li>Make sure MySQL is running in XAMPP Control Panel</li>
            <li>Check database credentials in <code>config/database.php</code></li>
            <li>Verify MySQL username is 'root' and password is blank</li>
        </ul>
    </div>";
}

echo "</div></body></html>";
?>


