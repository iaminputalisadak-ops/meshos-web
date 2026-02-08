<?php
/**
 * Test Login API - Diagnostic Tool
 * This helps identify what's wrong with the login API
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login API Test</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .success { color: #4CAF50; padding: 10px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; }
        .error { color: #f44336; padding: 10px; background: #ffebee; border-radius: 5px; margin: 10px 0; }
        .info { color: #2196F3; padding: 10px; background: #e3f2fd; border-radius: 5px; margin: 10px 0; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🔍 Login API Diagnostic Test</h1>
        
        <?php
        echo "<h2>1. Checking Database Connection...</h2>";
        try {
            require_once '../config/database.php';
            $conn = getDBConnection();
            echo "<div class='success'>✅ Database connection successful!</div>";
            
            // Check if admin_users table exists
            $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
            if ($tableCheck->num_rows === 0) {
                echo "<div class='error'>❌ admin_users table does NOT exist!</div>";
                echo "<div class='info'>👉 <a href='../database/setup_database.php' target='_blank'>Run Database Setup</a></div>";
            } else {
                echo "<div class='success'>✅ admin_users table exists!</div>";
                
                // Check if admin user exists
                $userCheck = $conn->query("SELECT COUNT(*) as count FROM admin_users");
                $userCount = $userCheck->fetch_assoc()['count'];
                if ($userCount === 0) {
                    echo "<div class='error'>❌ No admin users found in database!</div>";
                    echo "<div class='info'>👉 <a href='../database/setup_database.php' target='_blank'>Run Database Setup</a> to create admin user</div>";
                } else {
                    echo "<div class='success'>✅ Found $userCount admin user(s)</div>";
                }
            }
            $conn->close();
        } catch (Exception $e) {
            echo "<div class='error'>❌ Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
        
        echo "<h2>2. Testing Login API File Paths...</h2>";
        $apiPath = __DIR__ . '/../api/admin/login.php';
        $proxyPath = __DIR__ . '/login_api.php';
        
        echo "<div class='info'>";
        echo "Login API Path: <code>" . htmlspecialchars($apiPath) . "</code><br>";
        echo "File exists: " . (file_exists($apiPath) ? "✅ Yes" : "❌ No") . "<br><br>";
        echo "Proxy API Path: <code>" . htmlspecialchars($proxyPath) . "</code><br>";
        echo "File exists: " . (file_exists($proxyPath) ? "✅ Yes" : "❌ No") . "<br>";
        echo "</div>";
        
        echo "<h2>3. Testing Login API Response...</h2>";
        echo "<div class='info'>Making a test POST request to login_api.php...</div>";
        
        // Test the login API
        $testData = [
            'username' => 'admin',
            'password' => 'admin123'
        ];
        
        $ch = curl_init();
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . 
               '://' . $_SERVER['HTTP_HOST'] . 
               dirname($_SERVER['PHP_SELF']) . '/login_api.php';
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_HEADER, true);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        
        curl_close($ch);
        
        echo "<div class='info'>";
        echo "HTTP Status Code: <code>$httpCode</code><br>";
        echo "Response Headers:<br>";
        echo "<pre>" . htmlspecialchars($headers) . "</pre>";
        echo "Response Body:<br>";
        echo "<pre>" . htmlspecialchars($body) . "</pre>";
        echo "</div>";
        
        // Try to parse JSON
        $jsonData = json_decode($body, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "<div class='success'>✅ Response is valid JSON!</div>";
            echo "<pre>" . print_r($jsonData, true) . "</pre>";
        } else {
            echo "<div class='error'>❌ Response is NOT valid JSON!</div>";
            echo "<div class='error'>JSON Error: " . json_last_error_msg() . "</div>";
            echo "<div class='info'>This is likely why the login form shows 'Invalid response from server'</div>";
        }
        
        echo "<h2>4. Direct API Test...</h2>";
        echo "<div class='info'>";
        echo "Try accessing the login API directly:<br>";
        echo "<a href='../api/admin/login.php' target='_blank'>Login API (Direct)</a><br>";
        echo "<a href='login_api.php' target='_blank'>Login API (Proxy)</a><br>";
        echo "</div>";
        ?>
        
        <h2>5. Quick Fixes</h2>
        <div class="info">
            <p><strong>If database is not set up:</strong></p>
            <ol>
                <li><a href="../database/setup_database.php" target="_blank">Run Database Setup</a></li>
                <li>Wait for "Setup Complete!" message</li>
                <li>Refresh this page</li>
            </ol>
            
            <p><strong>If API returns HTML instead of JSON:</strong></p>
            <ol>
                <li>Check PHP error logs in XAMPP</li>
                <li>Make sure all PHP files have proper error handling</li>
                <li>Check that no output is sent before JSON headers</li>
            </ol>
        </div>
    </div>
</body>
</html>

