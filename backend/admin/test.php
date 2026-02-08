<?php
/**
 * Simple Test - Check if login handler works
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #4CAF50; padding: 15px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; }
        .error { color: #f44336; padding: 15px; background: #ffebee; border-radius: 5px; margin: 10px 0; }
        button { padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🧪 Test Login Handler</h1>
        <button onclick="test()">Test Login</button>
        <div id="result"></div>
    </div>
    
    <script>
        async function test() {
            const result = document.getElementById('result');
            result.innerHTML = '<div>Testing...</div>';
            
            try {
                const res = await fetch('login_handler.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({username: 'admin', password: 'admin123'})
                });
                
                const text = await res.text();
                result.innerHTML = '<div><strong>Status:</strong> ' + res.status + '</div>';
                result.innerHTML += '<div><strong>Response:</strong><pre>' + text + '</pre></div>';
                
                try {
                    const data = JSON.parse(text);
                    if (data.success) {
                        result.innerHTML = '<div class="success">✅ Login works!<pre>' + JSON.stringify(data, null, 2) + '</pre></div>';
                    } else {
                        result.innerHTML = '<div class="error">❌ Login failed: ' + data.message + '</div>';
                    }
                } catch(e) {
                    result.innerHTML = '<div class="error">❌ Not valid JSON: ' + e.message + '</div>';
                }
            } catch(e) {
                result.innerHTML = '<div class="error">❌ Error: ' + e.message + '</div>';
            }
        }
    </script>
</body>
</html>

