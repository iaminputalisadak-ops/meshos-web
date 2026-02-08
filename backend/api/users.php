<?php
/**
 * Users API Endpoint
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../config/error_handler.php';

$conn = getDBConnection();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'GET') {
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;
        $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
        
        $stmt = $conn->prepare("SELECT * FROM users ORDER BY created_at DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $users = [];
        while ($row = $result->fetch_assoc()) {
            unset($row['password']); // Don't send password
            $users[] = $row;
        }
        
        // Get total count
        $countResult = $conn->query("SELECT COUNT(*) as total FROM users");
        $total = $countResult->fetch_assoc()['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $users,
            'total' => $total
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

