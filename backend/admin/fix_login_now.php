<?php
/**
 * FIX LOGIN - Complete Database and Login Setup
 * Run this: http://localhost/backend/admin/fix_login_now.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Fix Admin Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #4CAF50; padding: 15px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; border-left: 4px solid #4CAF50; }
        .error { color: #f44336; padding: 15px; background: #ffebee; border-radius: 5px; margin: 10px 0; border-left: 4px solid #f44336; }
        .info { color: #2196F3; padding: 15px; background: #e3f2fd; border-radius: 5px; margin: 10px 0; border-left: 4px solid #2196F3; }
        .btn { display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; font-weight: bold; }
        code { background: #f4f4f4; padding: 3px 8px; border-radius: 3px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔧 Fix Admin Login</h1>
        
        <?php
        $allGood = true;
        
        // Step 1: Check MySQL
        echo "<h2>Step 1: Checking MySQL...</h2>";
        try {
            $conn = new mysqli('localhost', 'root', '');
            if ($conn->connect_error) {
                throw new Exception("Cannot connect: " . $conn->connect_error);
            }
            echo "<div class='success'>✅ MySQL connected!</div>";
        } catch (Exception $e) {
            echo "<div class='error'>❌ MySQL error: " . htmlspecialchars($e->getMessage()) . "</div>";
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
            echo "<div class='info'>ℹ️ " . $conn->error . "</div>";
        }
        
        // Step 4: Create/Update admin user
        echo "<h2>Step 4: Creating Admin User...</h2>";
        $username = 'admin';
        $password = 'admin123';
        $email = 'admin@meesho.com';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
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
            } else {
                echo "<div class='error'>❌ Update failed: " . $conn->error . "</div>";
                $allGood = false;
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
                echo "<div class='error'>❌ Create failed: " . $conn->error . "</div>";
                $allGood = false;
            }
        }
        
        // Step 5: Test login handler
        echo "<h2>Step 5: Testing Login Handler...</h2>";
        $handlerPath = __DIR__ . '/login_handler.php';
        if (file_exists($handlerPath)) {
            echo "<div class='success'>✅ login_handler.php exists!</div>";
            
            // Test the handler
            echo "<div class='info'>Testing login API...</div>";
            
            // Simulate a login request
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $testData = json_encode(['username' => 'admin', 'password' => 'admin123']);
            
            // Use curl to test
            if (function_exists('curl_init')) {
                $ch = curl_init();
                $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
                       '://' . $_SERVER['HTTP_HOST'] . 
                       dirname($_SERVER['PHP_SELF']) . '/login_handler.php';
                
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $testData);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                echo "<div class='info'>HTTP Status: <code>$httpCode</code></div>";
                echo "<div class='info'>Response: <pre>" . htmlspecialchars($response) . "</pre></div>";
                
                $jsonData = json_decode($response, true);
                if ($jsonData && isset($jsonData['success'])) {
                    if ($jsonData['success']) {
                        echo "<div class='success'>✅ Login handler works!</div>";
                    } else {
                        echo "<div class='error'>❌ Login handler error: " . ($jsonData['message'] ?? 'Unknown error') . "</div>";
                        $allGood = false;
                    }
                } else {
                    echo "<div class='error'>❌ Invalid JSON response from login handler</div>";
                    $allGood = false;
                }
            } else {
                echo "<div class='info'>ℹ️ cURL not available, skipping handler test</div>";
            }
        } else {
            echo "<div class='error'>❌ login_handler.php not found!</div>";
            $allGood = false;
        }
        
        $conn->close();
        
        if ($allGood) {
            echo "<div class='success'>
                <h2>🎉 Setup Complete!</h2>
                <p><strong>Admin Credentials:</strong></p>
                <p>Username: <code>$username</code><br>
                Password: <code>$password</code></p>
                <p><a href='index.php' class='btn'>Go to Admin Login</a></p>
            </div>";
        } else {
            echo "<div class='error'><h2>⚠️ Some Issues Found</h2><p>Please review errors above.</p></div>";
        }
        ?>
    </div>
</body>
</html>

