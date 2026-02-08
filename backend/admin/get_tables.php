<?php
/**
 * Get Database Tables Information
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once '../config/database.php';

header('Content-Type: application/json');

try {
    $conn = getDBConnection();
    
    // Get all tables
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    
    if ($result) {
        while ($row = $result->fetch_array()) {
            $tableName = $row[0];
            
            // Get row count
            $countResult = $conn->query("SELECT COUNT(*) as count FROM `$tableName`");
            $count = $countResult ? $countResult->fetch_assoc()['count'] : 0;
            
            // Get table size
            $sizeResult = $conn->query("
                SELECT 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE() 
                AND table_name = '$tableName'
            ");
            $size = $sizeResult ? $sizeResult->fetch_assoc()['size_mb'] . ' MB' : 'N/A';
            
            $tables[] = [
                'name' => $tableName,
                'rows' => $count,
                'size' => $size
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'tables' => $tables,
        'total' => count($tables)
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

