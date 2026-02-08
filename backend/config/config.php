<?php
/**
 * Main Configuration File
 * Centralized configuration for the application
 */

// Application Settings
define('APP_NAME', 'Meesho E-commerce');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, production

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'meesho_ecommerce');

// API Configuration
define('API_BASE_URL', 'http://localhost/backend/api');
define('API_VERSION', 'v1');

// CORS Configuration
define('ALLOWED_ORIGINS', [
    'http://localhost:3000',
    'http://localhost:3001',
    'http://127.0.0.1:3000'
]);

// Pagination
define('DEFAULT_LIMIT', 50);
define('MAX_LIMIT', 100);

// Error Reporting
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS
?>


