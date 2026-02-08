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
        // First try to connect without database to check MySQL is running
        $testConn = new mysqli(DB_HOST, DB_USER, DB_PASS);
        
        if ($testConn->connect_error) {
            throw new Exception("Cannot connect to MySQL server. Make sure MySQL is running in XAMPP. Error: " . $testConn->connect_error);
        }
        $testConn->close();
        
        // Now connect to the database
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            // Check if database doesn't exist
            if ($conn->connect_errno == 1049) {
                throw new Exception("Database '" . DB_NAME . "' does not exist. Please run the database setup first.");
            }
            throw new Exception("Connection failed: " . $conn->connect_error);
        }
        
        // Set charset to utf8mb4 for proper character encoding
        $conn->set_charset("utf8mb4");
        
        return $conn;
    } catch (Exception $e) {
        // Don't output JSON here if we're not in an API context
        // Let the calling code handle the error
        throw $e;
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

