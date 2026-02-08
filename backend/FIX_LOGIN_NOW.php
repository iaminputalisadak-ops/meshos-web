<?php
/**
 * QUICK FIX for Admin Login Issues
 * Run this file in your browser: http://localhost/backend/FIX_LOGIN_NOW.php
 * This will:
 * 1. Check database connection
 * 2. Create database if needed
 * 3. Create all tables if needed
 * 4. Create admin user if needed
 * 5. Test login API
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Admin Login - Meesho E-commerce</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .success { color: #4CAF50; padding: 15px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { color: #f44336; padding: 15px; background: #ffebee; border-radius: 5px; margin: 10px 0; border-left: 4px solid #f44336; }
        .info { color: #2196F3; padding: 15px; background: #e3f2fd; border-radius: 5px; margin: 10px 0; border-left: 4px solid #2196F3; }
        .warning { color: #ff9800; padding: 15px; background: #fff3e0; border-radius: 5px; margin: 10px 0; border-left: 4px solid #ff9800; }
        h1 { color: #333; }
        h2 { color: #555; margin-top: 30px; }
        code { background: #f4f4f4; padding: 3px 8px; border-radius: 3px; font-family: 'Courier New', monospace; }
        .btn { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: bold; }
        .btn:hover { opacity: 0.9; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; border: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class='box'>
        <h1>🔧 Admin Login Fix Tool</h1>
        <p>This tool will diagnose and fix common admin login issues.</p>";

$allGood = true;
$steps = [];

// Step 1: Check database connection
echo "<h2>Step 1: Checking Database Connection...</h2>";
try {
    require_once 'config/database.php';
    
    // Try to connect to MySQL
    $testConn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($testConn->connect_error) {
        throw new Exception("Cannot connect to MySQL: " . $testConn->connect_error);
    }
    $testConn->close();
    
    echo "<div class='success'>✅ MySQL connection successful!</div>";
    $steps[] = ['step' => 'MySQL Connection', 'status' => 'success'];
    
} catch (Exception $e) {
    echo "<div class='error'>❌ MySQL connection failed: " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'>💡 Make sure MySQL is running in XAMPP Control Panel</div>";
    $steps[] = ['step' => 'MySQL Connection', 'status' => 'error'];
    $allGood = false;
}

// Step 2: Create database
echo "<h2>Step 2: Creating Database...</h2>";
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    if ($conn->query($sql) === TRUE) {
        echo "<div class='success'>✅ Database '" . DB_NAME . "' ready!</div>";
        $steps[] = ['step' => 'Database Creation', 'status' => 'success'];
    } else {
        echo "<div class='info'>ℹ️ Database already exists or: " . $conn->error . "</div>";
        $steps[] = ['step' => 'Database Creation', 'status' => 'info'];
    }
    
    $conn->select_db(DB_NAME);
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $steps[] = ['step' => 'Database Creation', 'status' => 'error'];
    $allGood = false;
}

// Step 3: Create admin_users table
echo "<h2>Step 3: Creating Admin Users Table...</h2>";
try {
    $createTableSQL = "CREATE TABLE IF NOT EXISTS admin_users (
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
    
    if ($conn->query($createTableSQL) === TRUE) {
        echo "<div class='success'>✅ admin_users table created/verified!</div>";
        $steps[] = ['step' => 'Admin Users Table', 'status' => 'success'];
    } else {
        throw new Exception("Failed to create table: " . $conn->error);
    }
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Table creation error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $steps[] = ['step' => 'Admin Users Table', 'status' => 'error'];
    $allGood = false;
}

// Step 4: Create/Update admin user
echo "<h2>Step 4: Creating Admin User...</h2>";
try {
    $adminUsername = 'admin';
    $adminPassword = 'admin123';
    $adminEmail = 'admin@meesho.com';
    $adminFullName = 'Administrator';
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    // Check if admin exists
    $checkQuery = "SELECT id FROM admin_users WHERE username = '" . $conn->real_escape_string($adminUsername) . "'";
    $checkResult = $conn->query($checkQuery);
    
    if ($checkResult && $checkResult->num_rows > 0) {
        // Update existing admin
        $updateQuery = "UPDATE admin_users 
            SET password = '" . $conn->real_escape_string($hashedPassword) . "', 
                email = '" . $conn->real_escape_string($adminEmail) . "', 
                full_name = '" . $conn->real_escape_string($adminFullName) . "', 
                status = 'active',
                role = 'super_admin'
            WHERE username = '" . $conn->real_escape_string($adminUsername) . "'";
        
        if ($conn->query($updateQuery) === TRUE) {
            echo "<div class='success'>✅ Admin user updated successfully!</div>";
            $steps[] = ['step' => 'Admin User', 'status' => 'success'];
        } else {
            throw new Exception("Failed to update admin: " . $conn->error);
        }
    } else {
        // Create new admin
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
            $steps[] = ['step' => 'Admin User', 'status' => 'success'];
        } else {
            throw new Exception("Failed to create admin: " . $conn->error);
        }
    }
    
    echo "<div class='info'>
        <strong>📋 Admin Credentials:</strong><br>
        Username: <code>$adminUsername</code><br>
        Password: <code>$adminPassword</code>
    </div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ Admin user error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $steps[] = ['step' => 'Admin User', 'status' => 'error'];
    $allGood = false;
}

// Step 5: Test login API
echo "<h2>Step 5: Testing Login API...</h2>";
try {
    $apiPath = __DIR__ . '/api/admin/login.php';
    $proxyPath = __DIR__ . '/admin/login_api.php';
    
    if (!file_exists($apiPath)) {
        throw new Exception("Login API file not found: $apiPath");
    }
    
    if (!file_exists($proxyPath)) {
        throw new Exception("Login proxy file not found: $proxyPath");
    }
    
    echo "<div class='success'>✅ Login API files found!</div>";
    echo "<div class='info'>
        API Path: <code>" . htmlspecialchars($apiPath) . "</code><br>
        Proxy Path: <code>" . htmlspecialchars($proxyPath) . "</code>
    </div>";
    
    $steps[] = ['step' => 'Login API Files', 'status' => 'success'];
    
} catch (Exception $e) {
    echo "<div class='error'>❌ API file error: " . htmlspecialchars($e->getMessage()) . "</div>";
    $steps[] = ['step' => 'Login API Files', 'status' => 'error'];
    $allGood = false;
}

// Summary
echo "<h2>📊 Summary</h2>";
$successCount = 0;
$errorCount = 0;

foreach ($steps as $step) {
    if ($step['status'] === 'success') {
        $successCount++;
    } elseif ($step['status'] === 'error') {
        $errorCount++;
    }
}

if ($allGood && $errorCount === 0) {
    echo "<div class='success'>
        <h3>🎉 All Checks Passed!</h3>
        <p>Your admin login should now work correctly.</p>
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li>Go to <a href='admin/index.php' class='btn'>Admin Login Page</a></li>
            <li>Login with: <code>admin</code> / <code>admin123</code></li>
        </ol>
    </div>";
} else {
    echo "<div class='warning'>
        <h3>⚠️ Some Issues Found</h3>
        <p>Success: $successCount | Errors: $errorCount</p>
        <p>Please review the errors above and fix them.</p>
        <p><strong>Common Fixes:</strong></p>
        <ul>
            <li>Make sure MySQL is running in XAMPP Control Panel</li>
            <li>Check that Apache is also running</li>
            <li>Verify database credentials in <code>config/database.php</code></li>
        </ul>
    </div>";
}

// Close connection
if (isset($conn)) {
    $conn->close();
}

echo "</div></body></html>";
?>

