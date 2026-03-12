<?php
/**
 * Referral tracking API
 * GET: ref=CODE&product_id= (optional)
 * Validates promoter code, logs click, returns JSON (frontend can set cookie from response or cookie set by backend)
 * Cookie: ref_promo_id, ref_promo_code, ref_expiry (30 days)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

header('Content-Type: application/json; charset=utf-8');

$code = isset($_GET['ref']) ? trim($_GET['ref']) : '';
$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : null;

if ($code === '') {
    echo json_encode(['success' => false, 'message' => 'Missing ref parameter']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT id, code, status FROM promoter_profiles WHERE code = ? AND status = 'approved'");
$stmt->bind_param("s", $code);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Invalid or inactive referral code']);
    exit;
}

$promoter = $r->fetch_assoc();
$stmt->close();

$promoterId = (int) $promoter['id'];

// Log click
$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$sessionId = session_id() ?: (isset($_COOKIE['session_id']) ? $_COOKIE['session_id'] : null);
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null;
$ins = $conn->prepare("INSERT INTO referral_clicks (promoter_id, product_id, visitor_ip, visitor_session, user_agent) VALUES (?, ?, ?, ?, ?)");
$ins->bind_param("iisss", $promoterId, $productId, $ip, $sessionId, $ua);
$ins->execute();
$ins->close();

// Update promoter total_clicks
$conn->query("UPDATE promoter_profiles SET total_clicks = total_clicks + 1 WHERE id = $promoterId");

closeDBConnection($conn);

// Set cookie 30 days (last click wins - overwrite previous ref)
$expiry = time() + (30 * 86400);
setcookie('ref_promo_id', (string) $promoterId, $expiry, '/', '', false, true);
setcookie('ref_promo_code', $code, $expiry, '/', '', false, true);
setcookie('ref_expiry', (string) $expiry, $expiry, '/', '', false, true);

echo json_encode([
    'success' => true,
    'message' => 'Referral tracked',
    'data' => [
        'promoter_id' => $promoterId,
        'code' => $code,
        'expires_in_days' => 30
    ]
]);
