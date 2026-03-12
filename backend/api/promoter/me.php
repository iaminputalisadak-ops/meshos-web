<?php
/**
 * Current promoter info (must be logged in as promoter)
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
$stmt = $conn->prepare("SELECT pp.*, u.name, u.email, u.phone FROM promoter_profiles pp JOIN users u ON pp.user_id = u.id WHERE pp.id = ?");
$stmt->bind_param("i", $pid);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Promoter not found']);
    exit;
}
$row = $r->fetch_assoc();
$stmt->close();
closeDBConnection($conn);

unset($row['user_id']); // keep id as promoter_id for frontend
echo json_encode([
    'success' => true,
    'data' => [
        'promoter_id' => (int) $row['id'],
        'code' => $row['code'],
        'name' => $row['name'],
        'email' => $row['email'],
        'phone' => $row['phone'],
        'status' => $row['status'],
        'commission_rate' => (float) $row['commission_rate'],
        'total_clicks' => (int) $row['total_clicks'],
        'total_orders' => (int) $row['total_orders'],
        'total_sales' => (float) $row['total_sales'],
        'pending_commission' => (float) $row['pending_commission'],
        'approved_commission' => (float) $row['approved_commission'],
        'paid_commission' => (float) $row['paid_commission'],
    ]
]);
