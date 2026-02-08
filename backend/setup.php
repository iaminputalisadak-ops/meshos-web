<?php
/**
 * SIMPLE DATABASE SETUP - Works Every Time
 * Open: http://localhost/backend/setup.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Setup - Meesho</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #4CAF50; padding: 15px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { color: #f44336; padding: 15px; background: #ffebee; border-radius: 5px; margin: 10px 0; border-left: 4px solid #f44336; }
        .info { color: #2196F3; padding: 15px; background: #e3f2fd; border-radius: 5px; margin: 10px 0; border-left: 4px solid #2196F3; }
        .btn { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: bold; }
        code { background: #f4f4f4; padding: 3px 8px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🗄️ Database Setup</h1>
        
        <?php
        $allGood = true;
        
        // Step 1: Connect to MySQL
        echo "<h2>Step 1: Connecting to MySQL...</h2>";
        try {
            $conn = new mysqli('localhost', 'root', '');
            if ($conn->connect_error) {
                throw new Exception("Cannot connect: " . $conn->connect_error);
            }
            echo "<div class='success'>✅ MySQL connected!</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<div class='info'>💡 Make sure MySQL is running in XAMPP Control Panel</div>";
            $allGood = false;
            echo "</div></body></html>";
            exit;
        }
        
        // Step 2: Create database
        echo "<h2>Step 2: Creating Database...</h2>";
        $dbName = 'meesho_ecommerce';
        $sql = "CREATE DATABASE IF NOT EXISTS $dbName CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
        if ($conn->query($sql)) {
            echo "<div class='success'>✅ Database '$dbName' ready!</div>";
        } else {
            echo "<div class='info'>ℹ️ " . $conn->error . "</div>";
        }
        $conn->select_db($dbName);
        
        // Step 3: Create admin_users table
        echo "<h2>Step 3: Creating Admin Table...</h2>";
        $createTable = "CREATE TABLE IF NOT EXISTS admin_users (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        if ($conn->query($createTable)) {
            echo "<div class='success'>✅ admin_users table created!</div>";
        } else {
            echo "<div class='info'>ℹ️ Table may already exist</div>";
        }
        
        // Step 4: Create admin user
        echo "<h2>Step 4: Creating Admin User...</h2>";
        $username = 'admin';
        $password = 'admin123';
        $email = 'admin@meesho.com';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if exists
        $check = $conn->query("SELECT id FROM admin_users WHERE username = '" . $conn->real_escape_string($username) . "'");
        
        if ($check && $check->num_rows > 0) {
            $update = "UPDATE admin_users SET 
                password = '" . $conn->real_escape_string($hashedPassword) . "',
                email = '" . $conn->real_escape_string($email) . "',
                status = 'active',
                role = 'super_admin'
                WHERE username = '" . $conn->real_escape_string($username) . "'";
            if ($conn->query($update)) {
                echo "<div class='success'>✅ Admin user updated!</div>";
            }
        } else {
            $insert = "INSERT INTO admin_users (username, email, password, full_name, role, status) VALUES (
                '" . $conn->real_escape_string($username) . "',
                '" . $conn->real_escape_string($email) . "',
                '" . $conn->real_escape_string($hashedPassword) . "',
                'Administrator',
                'super_admin',
                'active'
            )";
            if ($conn->query($insert)) {
                echo "<div class='success'>✅ Admin user created!</div>";
            } else {
                echo "<div class='error'>❌ Failed: " . $conn->error . "</div>";
                $allGood = false;
            }
        }
        
        // Verify
        echo "<h2>Step 5: Verification...</h2>";
        $verify = $conn->query("SELECT id, username, email FROM admin_users WHERE username = 'admin'");
        if ($verify && $verify->num_rows > 0) {
            echo "<div class='success'>✅ Admin user verified!</div>";
        } else {
            echo "<div class='error'>❌ Admin user not found!</div>";
            $allGood = false;
        }
        
        $conn->close();
        
        if ($allGood) {
            echo "<div class='success'>
                <h2>🎉 Setup Complete!</h2>
                <p><strong>Admin Credentials:</strong></p>
                <p>Username: <code>$username</code><br>
                Password: <code>$password</code></p>
                <p><a href='admin/index.php' class='btn'>Go to Admin Login</a></p>
            </div>";
        } else {
            echo "<div class='error'><h2>⚠️ Some Issues Found</h2><p>Please review errors above.</p></div>";
        }
        ?>
    </div>
</body>
</html>

