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
    
    // Read and execute schema.sql
    $schemaFile = __DIR__ . '/schema.sql';
    if (file_exists($schemaFile)) {
        $sql = file_get_contents($schemaFile);
        
        // Remove CREATE DATABASE and USE statements (already done)
        $sql = preg_replace('/CREATE DATABASE.*?;/is', '', $sql);
        $sql = preg_replace('/USE.*?;/is', '', $sql);
        
        // Execute SQL statements
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            if (!empty($statement) && !preg_match('/^--/', $statement)) {
                if ($conn->query($statement) === TRUE) {
                    $successCount++;
                } else {
                    if (strpos($conn->error, 'already exists') === false) {
                        echo "<div class='error'>❌ Error: " . $conn->error . "</div>";
                        $errorCount++;
                    }
                }
            }
        }
        
        echo "<div class='success'>✅ Executed $successCount SQL statements successfully!</div>";
        if ($errorCount > 0) {
            echo "<div class='info'>ℹ️ Some statements may have already existed (this is normal)</div>";
        }
    } else {
        echo "<div class='error'>❌ Schema file not found: $schemaFile</div>";
    }
    
    // Create admin user
    echo "<h2>👤 Creating Admin User...</h2>";
    
    $adminUsername = 'admin';
    $adminPassword = 'admin123';
    $adminEmail = 'admin@meesho.com';
    $adminFullName = 'Administrator';
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    // Check if admin exists
    $checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    $checkStmt->bind_param("s", $adminUsername);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing admin
        $updateStmt = $conn->prepare("
            UPDATE admin_users 
            SET password = ?, email = ?, full_name = ?, status = 'active'
            WHERE username = ?
        ");
        $updateStmt->bind_param("ssss", $hashedPassword, $adminEmail, $adminFullName, $adminUsername);
        $updateStmt->execute();
        echo "<div class='success'>✅ Admin user updated successfully!</div>";
        $updateStmt->close();
    } else {
        // Create new admin
        $insertStmt = $conn->prepare("
            INSERT INTO admin_users (username, email, password, full_name, role, status)
            VALUES (?, ?, ?, ?, 'super_admin', 'active')
        ");
        $insertStmt->bind_param("ssss", $adminUsername, $adminEmail, $hashedPassword, $adminFullName);
        $insertStmt->execute();
        echo "<div class='success'>✅ Admin user created successfully!</div>";
        $insertStmt->close();
    }
    $checkStmt->close();
    
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


