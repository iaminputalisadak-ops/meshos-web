<?php
/**
 * EMERGENCY FIX - Complete Database and Admin Setup
 * Run this if nothing else works: http://localhost/backend/EMERGENCY_FIX.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html>
<html>
<head>
    <title>Emergency Fix - Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .success { color: #4CAF50; padding: 15px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { color: #f44336; padding: 15px; background: #ffebee; border-radius: 5px; margin: 10px 0; border-left: 4px solid #f44336; }
        .info { color: #2196F3; padding: 15px; background: #e3f2fd; border-radius: 5px; margin: 10px 0; border-left: 4px solid #2196F3; }
        .btn { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: bold; }
        code { background: #f4f4f4; padding: 3px 8px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class='box'>
        <h1>🚨 Emergency Fix - Complete Setup</h1>
        <p>This will completely set up your database and admin user.</p>";

$allFixed = true;

// Step 1: Database Connection
echo "<h2>Step 1: Database Connection</h2>";
try {
    require_once 'config/database.php';
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($conn->connect_error) {
        throw new Exception("Cannot connect to MySQL: " . $conn->connect_error);
    }
    echo "<div class='success'>✅ MySQL connected!</div>";
    
    // Create database
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->query($sql);
    $conn->select_db(DB_NAME);
    echo "<div class='success'>✅ Database '" . DB_NAME . "' ready!</div>";
    
} catch (Exception $e) {
    echo "<div class='error'>❌ " . htmlspecialchars($e->getMessage()) . "</div>";
    echo "<div class='info'>💡 Make sure MySQL is running in XAMPP Control Panel</div>";
    $allFixed = false;
    echo "</div></body></html>";
    exit;
}

// Step 2: Create admin_users table
echo "<h2>Step 2: Creating Admin Users Table</h2>";
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

if ($conn->query($createTable) === TRUE) {
    echo "<div class='success'>✅ admin_users table created!</div>";
} else {
    echo "<div class='info'>ℹ️ Table may already exist: " . $conn->error . "</div>";
}

// Step 3: Create/Update admin user
echo "<h2>Step 3: Creating Admin User</h2>";
$adminUsername = 'admin';
$adminPassword = 'admin123';
$adminEmail = 'admin@meesho.com';
$adminFullName = 'Administrator';
$hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);

// Check if exists
$check = $conn->query("SELECT id FROM admin_users WHERE username = '" . $conn->real_escape_string($adminUsername) . "'");

if ($check && $check->num_rows > 0) {
    // Update
    $update = "UPDATE admin_users SET 
        password = '" . $conn->real_escape_string($hashedPassword) . "',
        email = '" . $conn->real_escape_string($adminEmail) . "',
        full_name = '" . $conn->real_escape_string($adminFullName) . "',
        status = 'active',
        role = 'super_admin'
        WHERE username = '" . $conn->real_escape_string($adminUsername) . "'";
    
    if ($conn->query($update)) {
        echo "<div class='success'>✅ Admin user updated!</div>";
    } else {
        echo "<div class='error'>❌ Failed to update: " . $conn->error . "</div>";
        $allFixed = false;
    }
} else {
    // Insert
    $insert = "INSERT INTO admin_users (username, email, password, full_name, role, status) VALUES (
        '" . $conn->real_escape_string($adminUsername) . "',
        '" . $conn->real_escape_string($adminEmail) . "',
        '" . $conn->real_escape_string($hashedPassword) . "',
        '" . $conn->real_escape_string($adminFullName) . "',
        'super_admin',
        'active'
    )";
    
    if ($conn->query($insert)) {
        echo "<div class='success'>✅ Admin user created!</div>";
    } else {
        echo "<div class='error'>❌ Failed to create: " . $conn->error . "</div>";
        $allFixed = false;
    }
}

// Step 4: Verify
echo "<h2>Step 4: Verification</h2>";
$verify = $conn->query("SELECT id, username, email, status FROM admin_users WHERE username = 'admin'");
if ($verify && $verify->num_rows > 0) {
    $admin = $verify->fetch_assoc();
    echo "<div class='success'>✅ Admin user verified!</div>";
    echo "<div class='info'>
        <strong>Admin Details:</strong><br>
        ID: " . $admin['id'] . "<br>
        Username: " . htmlspecialchars($admin['username']) . "<br>
        Email: " . htmlspecialchars($admin['email']) . "<br>
        Status: " . htmlspecialchars($admin['status']) . "
    </div>";
} else {
    echo "<div class='error'>❌ Admin user not found!</div>";
    $allFixed = false;
}

$conn->close();

// Final message
if ($allFixed) {
    echo "<div class='success'>
        <h2>🎉 Setup Complete!</h2>
        <p><strong>Admin Credentials:</strong></p>
        <p>Username: <code>$adminUsername</code><br>
        Password: <code>$adminPassword</code></p>
        
        <p><strong>Next Steps:</strong></p>
        <ol>
            <li><a href='admin/index.php' class='btn'>Go to Admin Login</a></li>
            <li>Login with the credentials above</li>
            <li>You should be redirected to the dashboard</li>
        </ol>
        
        <p><strong>If login still doesn't work:</strong></p>
        <ul>
            <li><a href='admin/test_direct_login.php' target='_blank'>Test Direct Login</a> - This will show you exactly what's wrong</li>
            <li>Check browser console (F12) for JavaScript errors</li>
            <li>Make sure Apache is also running in XAMPP</li>
        </ul>
    </div>";
} else {
    echo "<div class='error'>
        <h2>⚠️ Some Issues Found</h2>
        <p>Please review the errors above and try again.</p>
        <p>Make sure MySQL is running in XAMPP Control Panel.</p>
    </div>";
}

echo "</div></body></html>";
?>

