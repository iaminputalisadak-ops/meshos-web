<?php
/**
 * Test Database Connection
 */
require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    // Test query
    $result = $conn->query("SELECT 1");
    
    // Check if admin_users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
    
    echo json_encode([
        'success' => true,
        'message' => 'Database connection successful',
        'database' => DB_NAME,
        'admin_table_exists' => $tableCheck->num_rows > 0
    ]);
    
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'error' => $e->getMessage(),
        'setup_url' => 'setup_database.php'
    ]);
}
?>

