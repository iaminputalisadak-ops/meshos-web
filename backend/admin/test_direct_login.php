<?php
/**
 * Test Direct Login - Quick test to verify login works
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Direct Login</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .success { color: #4CAF50; padding: 15px; background: #e8f5e9; border-radius: 5px; margin: 10px 0; }
        .error { color: #f44336; padding: 15px; background: #ffebee; border-radius: 5px; margin: 10px 0; }
        .info { color: #2196F3; padding: 15px; background: #e3f2fd; border-radius: 5px; margin: 10px 0; }
        button { padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; }
        button:hover { opacity: 0.9; }
        pre { background: #f4f4f4; padding: 15px; border-radius: 5px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="box">
        <h1>🧪 Test Direct Login</h1>
        <p>This will test the login_handler.php directly.</p>
        
        <button onclick="testLogin()">Test Login</button>
        
        <div id="result"></div>
    </div>
    
    <script>
        async function testLogin() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="info">Testing login...</div>';
            
            try {
                const response = await fetch('login_handler.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        username: 'admin',
                        password: 'admin123'
                    })
                });
                
                const responseText = await response.text();
                console.log('Response:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    resultDiv.innerHTML = `
                        <div class="error">
                            <strong>❌ JSON Parse Error:</strong><br>
                            ${e.message}<br><br>
                            <strong>Raw Response:</strong><br>
                            <pre>${responseText}</pre>
                        </div>
                    `;
                    return;
                }
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="success">
                            <strong>✅ Login Successful!</strong><br>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="error">
                            <strong>❌ Login Failed:</strong><br>
                            ${data.message}<br><br>
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="error">
                        <strong>❌ Error:</strong><br>
                        ${error.message}
                    </div>
                `;
            }
        }
    </script>
</body>
</html>

