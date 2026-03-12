<?php
/**
 * Orders API Endpoint
 * GET: list orders | POST: create order (from cart) with optional promoter attribution
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/cors.php';
require_once __DIR__ . '/../config/error_handler.php';

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
                pp.code as promoter_code,
                COUNT(oi.id) as items_count
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            LEFT JOIN promoter_profiles pp ON o.promoter_id = pp.id
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
        
        $countResult = $conn->query("SELECT COUNT(*) as total FROM orders");
        $total = $countResult->fetch_assoc()['total'];
        
        echo json_encode([
            'success' => true,
            'data' => $orders,
            'total' => (int) $total
        ]);
        
        $stmt->close();
    } elseif ($method === 'POST') {
        handleCreateOrder($conn);
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
        'data' => [],
        'total' => 0,
        'message' => $e->getMessage()
    ]);
} finally {
    closeDBConnection($conn);
}

/**
 * Create order from cart. Body: items[], total_amount, final_amount, discount_amount?, shipping_address?, payment_method?
 * Optional: promoter_id, referral_code (or read from cookie server-side).
 * Self-referral: if order user_id = promoter's user_id, do not attribute.
 */
function handleCreateOrder($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['items']) || !isset($data['final_amount']) || !isset($data['total_amount'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'items, total_amount and final_amount required']);
        return;
    }
    
    $userId = isset($data['user_id']) ? (int) $data['user_id'] : null;
    $sessionId = isset($data['session_id']) ? $data['session_id'] : (isset($_COOKIE['session_id']) ? $_COOKIE['session_id'] : bin2hex(random_bytes(16)));
    $totalAmount = (float) $data['total_amount'];
    $discountAmount = isset($data['discount_amount']) ? (float) $data['discount_amount'] : 0;
    $finalAmount = (float) $data['final_amount'];
    $shippingAddress = isset($data['shipping_address']) ? $data['shipping_address'] : '';
    $paymentMethod = isset($data['payment_method']) ? $data['payment_method'] : 'cod';
    
    $promoterId = isset($data['promoter_id']) ? (int) $data['promoter_id'] : (isset($_COOKIE['ref_promo_id']) ? (int) $_COOKIE['ref_promo_id'] : null);
    $referralCode = isset($data['referral_code']) ? trim($data['referral_code']) : (isset($_COOKIE['ref_promo_code']) ? trim($_COOKIE['ref_promo_code']) : null);
    
    if ($promoterId && $referralCode) {
        $chk = $conn->prepare("SELECT pp.id, pp.user_id FROM promoter_profiles pp WHERE pp.id = ? AND pp.code = ? AND pp.status = 'approved'");
        $chk->bind_param("is", $promoterId, $referralCode);
        $chk->execute();
        $pr = $chk->get_result()->fetch_assoc();
        $chk->close();
        if (!$pr) {
            $promoterId = null;
            $referralCode = null;
        } elseif ($userId && (int) $pr['user_id'] === $userId) {
            $promoterId = null;
            $referralCode = null;
        }
    } else {
        $promoterId = null;
        $referralCode = null;
    }
    
    $stmt = $conn->prepare("INSERT INTO orders (user_id, session_id, total_amount, discount_amount, final_amount, status, shipping_address, payment_method, promoter_id, referral_code) VALUES (?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?)");
    $stmt->bind_param("isdddssis", $userId, $sessionId, $totalAmount, $discountAmount, $finalAmount, $shippingAddress, $paymentMethod, $promoterId, $referralCode);
    if (!$stmt->execute()) {
        $stmt->close();
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create order']);
        return;
    }
    $orderId = $conn->insert_id;
    $stmt->close();
    
    foreach ($data['items'] as $item) {
        $pid = (int) ($item['product_id'] ?? $item['id']);
        $qty = (int) ($item['quantity'] ?? 1);
        $price = (float) ($item['price'] ?? 0);
        $disc = (float) ($item['discount'] ?? 0);
        $total = (float) ($item['subtotal'] ?? ($price * $qty));
        $ins = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount, total) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param("iiiddd", $orderId, $pid, $qty, $price, $disc, $total);
        $ins->execute();
        $ins->close();
    }
    
    if ($promoterId) {
        $rate = 10.0;
        $commAmount = round($finalAmount * ($rate / 100), 2);
        $insComm = $conn->prepare("INSERT INTO commissions (promoter_id, order_id, order_amount, commission_rate, commission_amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        $insComm->bind_param("iiddd", $promoterId, $orderId, $finalAmount, $rate, $commAmount);
        $insComm->execute();
        $insComm->close();
        $conn->query("UPDATE promoter_profiles SET total_orders = total_orders + 1, total_sales = total_sales + $finalAmount, pending_commission = pending_commission + $commAmount WHERE id = $promoterId");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'data' => ['order_id' => (int) $orderId]
    ]);
}
?>

