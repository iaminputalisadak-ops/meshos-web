<?php
/**
 * Get current referral from cookie (for checkout/order to attach promoter)
 * GET: returns { success, data: { promoter_id, code } or null }
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

header('Content-Type: application/json; charset=utf-8');

$promoterId = isset($_COOKIE['ref_promo_id']) ? (int) $_COOKIE['ref_promo_id'] : 0;
$expiry = isset($_COOKIE['ref_expiry']) ? (int) $_COOKIE['ref_expiry'] : 0;
$code = isset($_COOKIE['ref_promo_code']) ? trim($_COOKIE['ref_promo_code']) : '';

if ($promoterId < 1 || $expiry < time() || $code === '') {
    echo json_encode(['success' => true, 'data' => null]);
    exit;
}

$conn = getDBConnection();
$stmt = $conn->prepare("SELECT id, code FROM promoter_profiles WHERE id = ? AND code = ? AND status = 'approved'");
$stmt->bind_param("is", $promoterId, $code);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    echo json_encode(['success' => true, 'data' => null]);
    exit;
}
$row = $r->fetch_assoc();
$stmt->close();
closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => [
        'promoter_id' => (int) $row['id'],
        'code' => $row['code']
    ]
]);
