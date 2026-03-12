<?php
/**
 * Promoter dashboard: stats + product list with referral links
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

header('Content-Type: application/json; charset=utf-8');
session_start();

if (empty($_SESSION['promoter_logged_in']) || empty($_SESSION['promoter_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in as promoter']);
    exit;
}

$conn = getDBConnection();
$pid = (int) $_SESSION['promoter_id'];
$code = $_SESSION['promoter_code'];

// Base URL for referral links (frontend can override with REACT_APP_BASE_URL)
$baseUrl = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : 'http://localhost:3000';

// Promoter stats
$stmt = $conn->prepare("SELECT total_clicks, total_orders, total_sales, pending_commission, approved_commission, paid_commission FROM promoter_profiles WHERE id = ?");
$stmt->bind_param("i", $pid);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Recent commissions
$stmt2 = $conn->prepare("SELECT c.id, c.order_id, c.order_amount, c.commission_amount, c.status, c.created_at FROM commissions c WHERE c.promoter_id = ? ORDER BY c.created_at DESC LIMIT 20");
$stmt2->bind_param("i", $pid);
$stmt2->execute();
$res = $stmt2->get_result();
$commissions = [];
while ($row = $res->fetch_assoc()) {
    $commissions[] = $row;
}
$stmt2->close();

// Products with referral links
$res2 = $conn->query("SELECT id, name, price, image FROM products WHERE in_stock = 1 ORDER BY name LIMIT 100");
$products = [];
while ($row = $res2->fetch_assoc()) {
    $row['referral_link'] = $baseUrl . '/product/' . $row['id'] . '?ref=' . urlencode($code);
    $products[] = $row;
}

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => [
        'stats' => $stats,
        'promoter_code' => $code,
        'commissions' => $commissions,
        'products' => $products,
        'base_url' => $baseUrl
    ]
]);
