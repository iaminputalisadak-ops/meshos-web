<?php
/**
 * Orders API Endpoint
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
        
        $stmt = $conn->prepare("
            SELECT 
                o.*,
                u.name as customer_name,
                COUNT(oi.id) as items_count
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.order_id
            GROUP BY o.id
            ORDER BY o.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $orders = [];
        while ($row = $result->fetch_assoc()) {
            $orders[] = $row;
        }
        
        // Get total count
        $countResult = $conn->query("SELECT COUNT(*) as total FROM orders");
        $total = $countResult->fetch_assoc()['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $orders,
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
        'success' => true,
        'data' => [],
        'total' => 0,
        'message' => 'No orders yet'
    ]);
} finally {
    closeDBConnection($conn);
}
?>

