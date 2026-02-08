<?php
/**
 * Server Status Check
 * Access this file to verify PHP server is working
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Server Status Check</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .status-box {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success {
            color: #4CAF50;
            font-size: 24px;
            font-weight: bold;
        }
        .info {
            margin-top: 20px;
            padding: 15px;
            background: #e3f2fd;
            border-radius: 5px;
        }
        a {
            color: #2196F3;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="status-box">
        <h1>✅ PHP Server is Running!</h1>
        <p class="success">Server Status: ACTIVE</p>
        
        <div class="info">
            <h3>Quick Links:</h3>
            <ul>
                <li><a href="admin/index.php">Admin Login Panel</a></li>
                <li><a href="api/products.php">Products API</a></li>
                <li><a href="api/categories.php">Categories API</a></li>
                <li><a href="api/index.php">API Documentation</a></li>
            </ul>
            
            <h3>Admin Credentials:</h3>
            <p><strong>Username:</strong> admin<br>
            <strong>Password:</strong> admin123</p>
            
            <h3>Server Information:</h3>
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?><br>
            <strong>Server:</strong> PHP Built-in Server<br>
            <strong>Document Root:</strong> <?php echo $_SERVER['DOCUMENT_ROOT']; ?></p>
        </div>
    </div>
</body>
</html>


