<?php
/**
 * Admin: Update order status. On delivered -> approve commission; on cancelled -> reject commission.
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$orderId = (int) ($input['order_id'] ?? $_GET['order_id'] ?? 0);
$newStatus = trim($input['status'] ?? $_GET['status'] ?? '');

$allowed = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
if (!$orderId || !in_array($newStatus, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valid order_id and status required']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT id, promoter_id, status FROM orders WHERE id = ?");
$stmt->bind_param("i", $orderId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    closeDBConnection($conn);
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Order not found']);
    exit;
}

$conn->query("UPDATE orders SET status = '$newStatus' WHERE id = $orderId");

if (!empty($order['promoter_id'])) {
    $pid = (int) $order['promoter_id'];
    if ($newStatus === 'delivered') {
        $conn->query("UPDATE commissions SET status = 'approved', approved_at = NOW() WHERE order_id = $orderId AND status = 'pending'");
        $row = $conn->query("SELECT commission_amount FROM commissions WHERE order_id = $orderId")->fetch_assoc();
        if ($row) {
            $amt = (float) $row['commission_amount'];
            $conn->query("UPDATE promoter_profiles SET pending_commission = pending_commission - $amt, approved_commission = approved_commission + $amt WHERE id = $pid");
        }
    } elseif ($newStatus === 'cancelled') {
        $conn->query("UPDATE commissions SET status = 'rejected' WHERE order_id = $orderId AND status = 'pending'");
        $row = $conn->query("SELECT commission_amount FROM commissions WHERE order_id = $orderId")->fetch_assoc();
        if ($row) {
            $amt = (float) $row['commission_amount'];
            $conn->query("UPDATE promoter_profiles SET pending_commission = pending_commission - $amt, total_orders = total_orders - 1 WHERE id = $pid");
        }
    }
}

closeDBConnection($conn);
echo json_encode(['success' => true, 'message' => 'Order status updated', 'data' => ['order_id' => $orderId, 'status' => $newStatus]]);
