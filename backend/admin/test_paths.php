<?php
/**
 * Test API Paths - Debug tool
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test API Paths</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .pass { color: green; }
        .fail { color: red; }
        pre { background: #f0f0f0; padding: 10px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>API Path Test</h1>
    
    <div class="test">
        <h3>Current Location</h3>
        <pre>
Script: <?php echo __FILE__; ?>
Document Root: <?php echo $_SERVER['DOCUMENT_ROOT']; ?>
Script Name: <?php echo $_SERVER['SCRIPT_NAME']; ?>
Request URI: <?php echo $_SERVER['REQUEST_URI']; ?>
        </pre>
    </div>
    
    <div class="test">
        <h3>File Existence Check</h3>
        <?php
        $files = [
            '../api/admin/login.php' => 'Relative from admin folder',
            __DIR__ . '/../api/admin/login.php' => 'Absolute path',
            $_SERVER['DOCUMENT_ROOT'] . '/backend/api/admin/login.php' => 'From document root'
        ];
        
        foreach ($files as $file => $desc) {
            $exists = file_exists($file);
            $class = $exists ? 'pass' : 'fail';
            echo "<div class='$class'>";
            echo "<strong>$desc:</strong><br>";
            echo "Path: $file<br>";
            echo "Exists: " . ($exists ? 'YES ✅' : 'NO ❌');
            echo "</div><br>";
        }
        ?>
    </div>
    
    <div class="test">
        <h3>Test API Call</h3>
        <button onclick="testAPI()">Test Login API</button>
        <div id="result"></div>
    </div>
    
    <script>
        function testAPI() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = 'Testing...';
            
            // Try different paths
            const paths = [
                '../api/admin/login.php',
                '/backend/api/admin/login.php',
                window.location.origin + '/backend/api/admin/login.php',
                window.location.pathname.replace('/admin/test_paths.php', '') + '/api/admin/login.php'
            ];
            
            let html = '<h4>Trying paths:</h4><ul>';
            
            paths.forEach((path, index) => {
                html += `<li>Path ${index + 1}: <code>${path}</code></li>`;
                
                fetch(path, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ username: 'test', password: 'test' })
                })
                .then(res => {
                    html += `<li class="pass">✅ Path ${index + 1} - Status: ${res.status}</li>`;
                    resultDiv.innerHTML = html;
                })
                .catch(err => {
                    html += `<li class="fail">❌ Path ${index + 1} - Error: ${err.message}</li>`;
                    resultDiv.innerHTML = html;
                });
            });
            
            resultDiv.innerHTML = html;
        }
    </script>
</body>
</html>

