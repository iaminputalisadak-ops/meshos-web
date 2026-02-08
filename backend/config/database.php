<?php
/**
 * Database Configuration
 * Update these values according to your MySQL setup
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // Blank password for XAMPP default
define('DB_NAME', 'meesho_ecommerce');

/**
 * Create database connection
 */
function getDBConnection() {
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4 for proper character encoding
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

/**
 * Close database connection
 */
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>

