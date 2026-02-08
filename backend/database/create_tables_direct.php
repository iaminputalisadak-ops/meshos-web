<?php
/**
 * Direct Table Creation - Alternative method
 * Creates all tables directly without parsing SQL file
 */
require_once '../config/database.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Create Tables - Meesho E-commerce</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #4CAF50; padding: 10px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; }
        .error { color: #f44336; padding: 10px; background: #ffebee; border-radius: 5px; margin: 10px 0; }
        .info { color: #2196F3; padding: 10px; background: #e3f2fd; border-radius: 5px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class='box'>
        <h1>🗄️ Create Database Tables</h1>";

try {
    $conn = getDBConnection();
    
    // Define all tables
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
            FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
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
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
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
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
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
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
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
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
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
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
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
    
    $created = 0;
    $skipped = 0;
    $errors = 0;
    
    foreach ($tables as $tableName => $createSQL) {
        if ($conn->query($createSQL) === TRUE) {
            // Check if table was actually created or already existed
            $check = $conn->query("SHOW TABLES LIKE '$tableName'");
            if ($check->num_rows > 0) {
                $created++;
                echo "<div class='success'>✅ Table '$tableName' created/verified</div>";
            }
        } else {
            $error = $conn->error;
            if (strpos($error, 'already exists') !== false) {
                $skipped++;
                echo "<div class='info'>ℹ️ Table '$tableName' already exists</div>";
            } else {
                $errors++;
                echo "<div class='error'>❌ Error creating '$tableName': " . htmlspecialchars($error) . "</div>";
            }
        }
    }
    
    // Insert default data
    echo "<h2>📊 Inserting Default Data...</h2>";
    
    // Insert categories
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
    
    foreach ($categories as $cat) {
        $insert = "INSERT IGNORE INTO categories (name, slug) VALUES ('" . $conn->real_escape_string($cat[0]) . "', '" . $conn->real_escape_string($cat[1]) . "')";
        $conn->query($insert);
    }
    echo "<div class='success'>✅ Categories inserted</div>";
    
    // Create admin user
    $adminUsername = 'admin';
    $adminPassword = 'admin123';
    $adminEmail = 'admin@meesho.com';
    $adminFullName = 'Administrator';
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    $checkAdmin = $conn->query("SELECT id FROM admin_users WHERE username = '" . $conn->real_escape_string($adminUsername) . "'");
    if ($checkAdmin->num_rows === 0) {
        $insertAdmin = "INSERT INTO admin_users (username, email, password, full_name, role, status) VALUES (
            '" . $conn->real_escape_string($adminUsername) . "',
            '" . $conn->real_escape_string($adminEmail) . "',
            '" . $conn->real_escape_string($hashedPassword) . "',
            '" . $conn->real_escape_string($adminFullName) . "',
            'super_admin',
            'active'
        )";
        if ($conn->query($insertAdmin)) {
            echo "<div class='success'>✅ Admin user created</div>";
        }
    } else {
        // Update admin password
        $updateAdmin = "UPDATE admin_users SET password = '" . $conn->real_escape_string($hashedPassword) . "' WHERE username = '" . $conn->real_escape_string($adminUsername) . "'";
        $conn->query($updateAdmin);
        echo "<div class='success'>✅ Admin user updated</div>";
    }
    
    echo "<div class='info'>
        <h3>📋 Admin Credentials:</h3>
        <p><strong>Username:</strong> <code>$adminUsername</code></p>
        <p><strong>Password:</strong> <code>$adminPassword</code></p>
    </div>";
    
    echo "<div class='success'>
        <h3>🎉 Setup Complete!</h3>
        <p>Created: $created tables | Skipped: $skipped | Errors: $errors</p>
        <p><a href='../admin/index.php' style='color: #2196F3;'>Go to Admin Panel</a></p>
    </div>";
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>

