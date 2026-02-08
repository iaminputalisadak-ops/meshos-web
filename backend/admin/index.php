<?php
/**
 * Admin Panel - Login Page
 */
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Meesho E-commerce</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 8px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        
        .success-message {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }
        
        .loading {
            display: none;
            text-align: center;
            color: #667eea;
            margin-top: 10px;
        }
        
        .default-credentials {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
        
        .default-credentials strong {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Admin Panel</h1>
            <p>Meesho E-commerce</p>
        </div>
        
        <?php if (isset($_GET['logout']) && $_GET['logout'] === 'success'): ?>
            <div class="success-message" id="successMessage" style="display: block;">
                <i class="fas fa-check-circle"></i> 
                You have been successfully logged out<?php echo isset($_GET['user']) ? ' (' . htmlspecialchars($_GET['user']) . ')' : ''; ?>.
            </div>
        <?php else: ?>
            <div class="error-message" id="errorMessage"></div>
            <div class="success-message" id="successMessage"></div>
        <?php endif; ?>
        
        <form id="loginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required autocomplete="username">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required autocomplete="current-password">
            </div>
            
            <button type="submit" class="btn-login">Login</button>
            
            <div class="loading" id="loading">Logging in...</div>
        </form>
        
        <div class="default-credentials">
            <strong>Default Credentials:</strong><br>
            Username: <code>admin</code><br>
            Password: <code>admin123</code>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: #fff3e0; border-radius: 8px; border-left: 4px solid #ff9800;">
            <strong>⚠️ Having Login Issues?</strong><br>
            <small>
                <a href="fix_login_now.php" target="_blank" style="color: #667eea; text-decoration: underline; font-weight: bold;">👉 Fix Login Now</a> | 
                <a href="../setup.php" target="_blank" style="color: #667eea; text-decoration: underline;">Database Setup</a> | 
                <a href="test.php" target="_blank" style="color: #667eea; text-decoration: underline;">Test Login</a>
            </small>
        </div>
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');
            const loadingDiv = document.getElementById('loading');
            
            // Hide previous messages
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            loadingDiv.style.display = 'block';
            
            try {
                // Clear any previous error
                errorDiv.innerHTML = '';
                
                // Use direct login handler (self-contained, no path issues)
                const loginUrl = 'login_handler.php';
                
                console.log('Attempting login to:', loginUrl);
                
                const response = await fetch(loginUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    credentials: 'include',
                    mode: 'cors',
                    body: JSON.stringify({ 
                        username: username.trim(), 
                        password: password 
                    })
                });
                
                // Check if response is OK
                if (!response.ok) {
                    if (response.status === 404) {
                        throw new Error(`API not found (404). Check if file exists at: ${loginUrl}`);
                    }
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                let data;
                let responseText = '';
                try {
                    responseText = await response.text();
                    console.log('Raw response:', responseText);
                    
                    // Try to parse JSON
                    if (responseText.trim() === '') {
                        throw new Error('Empty response from server');
                    }
                    
                    data = JSON.parse(responseText);
                } catch (jsonError) {
                    loadingDiv.style.display = 'none';
                    let errorMsg = 'Invalid response from server. ';
                    
                    // Check if it's a database issue
                    if (jsonError.message.includes('Empty') || 
                        (responseText && (responseText.includes('database') || responseText.includes('table') || responseText.includes('MySQL')))) {
                        errorMsg += 'The database may not be set up. ';
                    }
                    
                    errorMsg += '<br><br>';
                    errorMsg += '<a href="fix_login_now.php" target="_blank" style="color: #667eea; text-decoration: underline; font-weight: bold; margin-right: 10px;">👉 Fix Login Now</a>';
                    errorMsg += '<a href="../setup.php" target="_blank" style="color: #667eea; text-decoration: underline; font-weight: bold;">👉 Run Database Setup</a>';
                    
                    errorDiv.innerHTML = errorMsg;
                    errorDiv.style.display = 'block';
                    console.error('JSON Parse Error:', jsonError);
                    console.error('Response text:', responseText);
                    return;
                }
                
                loadingDiv.style.display = 'none';
                
                if (data.success) {
                    successDiv.textContent = 'Login successful! Redirecting...';
                    successDiv.style.display = 'block';
                    
                    // Redirect to dashboard
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1000);
                } else {
                    let errorMsg = data.message || 'Login failed. Please check your credentials.';
                    
                    // Check if database setup is needed
                    if (data.setup_url) {
                        errorMsg += ' <a href="' + data.setup_url + '" target="_blank" style="color: #667eea; text-decoration: underline;">Setup Database</a>';
                    }
                    
                    // Show additional error details if available
                    if (data.error) {
                        console.error('Server Error:', data.error);
                    }
                    
                    errorDiv.innerHTML = errorMsg;
                    errorDiv.style.display = 'block';
                }
            } catch (error) {
                loadingDiv.style.display = 'none';
                let errorMsg = 'Connection error. ';
                
                // Try to get more details from error
                if (error.message) {
                    errorMsg += error.message;
                }
                
                // Add helpful links
                errorMsg += '<br><br>';
                errorMsg += '<a href="../EMERGENCY_FIX.php" target="_blank" style="color: #667eea; text-decoration: underline; font-weight: bold;">👉 Run Emergency Fix</a> | ';
                errorMsg += '<a href="test_direct_login.php" target="_blank" style="color: #667eea; text-decoration: underline;">Test Login API</a>';
                
                errorDiv.innerHTML = errorMsg;
                errorDiv.style.display = 'block';
                console.error('Error:', error);
            }
        });
        
        // Check database setup first - use relative path
        fetch('check_setup.php')
        .then(res => {
            if (!res.ok) {
                throw new Error('Setup check failed: ' + res.status);
            }
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                const errorDiv = document.getElementById('errorMessage');
                let errorMsg = data.message || 'Database setup required';
                if (data.setup_url) {
                    errorMsg += ' <a href="' + data.setup_url + '" target="_blank" style="color: #667eea; text-decoration: underline; font-weight: bold; margin-left: 10px;">👉 Setup Database Now</a>';
                } else {
                    errorMsg += ' <a href="../setup.php" target="_blank" style="color: #667eea; text-decoration: underline; font-weight: bold; margin-left: 10px;">👉 Setup Database Now</a>';
                }
                errorDiv.innerHTML = errorMsg;
                errorDiv.style.display = 'block';
            }
        })
        .catch(err => {
            console.error('Setup check error:', err);
            const errorDiv = document.getElementById('errorMessage');
            errorDiv.innerHTML = 'Cannot check database status. <a href="fix_login_now.php" target="_blank" style="color: #667eea; text-decoration: underline; font-weight: bold; margin-right: 10px;">👉 Fix Login Now</a><a href="../setup.php" target="_blank" style="color: #667eea; text-decoration: underline; font-weight: bold;">👉 Setup Database</a>';
            errorDiv.style.display = 'block';
        });
        
        // Check if already logged in - use relative path
        fetch('../api/admin/auth.php', {
            credentials: 'include'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.authenticated) {
                window.location.href = 'dashboard.php';
            }
        })
        .catch(err => console.error('Auth check error:', err));
    </script>
</body>
</html>


