<?php
/**
 * Subscriptions API Endpoint
 * Handles subscription operations
 */

require_once '../config/database.php';
require_once '../config/cors.php';
require_once '../config/error_handler.php';

$conn = getDBConnection();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    switch ($method) {
        case 'GET':
            handleGetSubscription($conn);
            break;
        case 'POST':
            handleCreateSubscription($conn);
            break;
        case 'PUT':
            handleUpdateSubscription($conn);
            break;
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
            break;
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

/**
 * Get user subscription
 */
function handleGetSubscription($conn) {
    $userId = isset($_GET['user_id']) ? intval($_GET['user_id']) : null;
    
    if (!$userId) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'User ID is required'
        ]);
        return;
    }
    
    $stmt = $conn->prepare("
        SELECT * FROM subscriptions 
        WHERE user_id = ? AND status = 'active'
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => true,
            'data' => null,
            'message' => 'No active subscription found'
        ]);
        $stmt->close();
        return;
    }
    
    $subscription = $result->fetch_assoc();
    $subscription['price'] = floatval($subscription['price']);
    $subscription['discount_percentage'] = intval($subscription['discount_percentage']);
    
    echo json_encode([
        'success' => true,
        'data' => $subscription
    ]);
    
    $stmt->close();
}

/**
 * Create subscription
 */
function handleCreateSubscription($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['user_id']) || !isset($data['plan_type']) || !isset($data['price'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'User ID, plan type, and price are required'
        ]);
        return;
    }
    
    $userId = intval($data['user_id']);
    $planType = $data['plan_type'];
    $price = floatval($data['price']);
    $discountPercentage = isset($data['discount_percentage']) ? intval($data['discount_percentage']) : 0;
    
    // Cancel existing active subscriptions
    $cancelStmt = $conn->prepare("UPDATE subscriptions SET status = 'cancelled' WHERE user_id = ? AND status = 'active'");
    $cancelStmt->bind_param("i", $userId);
    $cancelStmt->execute();
    $cancelStmt->close();
    
    // Create new subscription
    $startDate = date('Y-m-d');
    $endDate = date('Y-m-d', strtotime('+1 year'));
    
    $stmt = $conn->prepare("
        INSERT INTO subscriptions (user_id, plan_type, price, discount_percentage, start_date, end_date, status)
        VALUES (?, ?, ?, ?, ?, ?, 'active')
    ");
    $stmt->bind_param("isdiiss", $userId, $planType, $price, $discountPercentage, $startDate, $endDate);
    $stmt->execute();
    
    $subscriptionId = $conn->insert_id;
    
    echo json_encode([
        'success' => true,
        'message' => 'Subscription created successfully',
        'data' => [
            'id' => $subscriptionId,
            'plan_type' => $planType,
            'price' => $price,
            'discount_percentage' => $discountPercentage
        ]
    ]);
    
    $stmt->close();
}

/**
 * Update subscription (cancel)
 */
function handleUpdateSubscription($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['subscription_id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Subscription ID is required'
        ]);
        return;
    }
    
    $subscriptionId = intval($data['subscription_id']);
    $status = isset($data['status']) ? $data['status'] : 'cancelled';
    
    $stmt = $conn->prepare("UPDATE subscriptions SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $subscriptionId);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'message' => 'Subscription updated successfully'
    ]);
}
?>


