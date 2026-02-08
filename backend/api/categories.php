<?php
/**
 * Categories API Endpoint
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../config/error_handler.php';

$conn = getDBConnection();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $stmt = $conn->query("SELECT * FROM categories ORDER BY name ASC");
        $categories = [];
        
        while ($row = $stmt->fetch_assoc()) {
            $categories[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'data' => $categories
        ]);
        
        $stmt->close();
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
} finally {
    closeDBConnection($conn);
}
?>


