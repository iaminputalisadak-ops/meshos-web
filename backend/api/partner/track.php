<?php
/**
 * Partner referral tracking API
 * GET: partner=CODE&product_id= (optional)
 * Validates creator code + active membership, logs click, sets cookie
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

header('Content-Type: application/json; charset=utf-8');

$code = isset($_GET['partner']) ? trim($_GET['partner']) : '';
$productId = isset($_GET['product_id']) ? (int) $_GET['product_id'] : null;

if ($code === '') {
    echo json_encode(['success' => false, 'message' => 'Missing partner parameter']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("
    SELECT cp.id, cp.creator_code, cp.active_status, cp.approval_status
    FROM creator_profiles cp
    WHERE cp.creator_code = ? AND cp.approval_status = 'approved'
");
$stmt->bind_param("s", $code);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    echo json_encode(['success' => false, 'message' => 'Invalid partner code']);
    exit;
}

$creator = $r->fetch_assoc();
$stmt->close();
$creatorId = (int) $creator['id'];

$memStmt = $conn->prepare("
    SELECT id, expires_at FROM creator_memberships
    WHERE creator_id = ? AND admin_approval = 'approved' AND payment_status = 'paid'
    ORDER BY expires_at DESC LIMIT 1
");
$memStmt->bind_param("i", $creatorId);
$memStmt->execute();
$mem = $memStmt->get_result()->fetch_assoc();
$memStmt->close();

$membershipId = null;
$isActive = false;
if ($mem && $mem['expires_at'] && strtotime($mem['expires_at']) > time()) {
    $isActive = true;
    $membershipId = (int) $mem['id'];
}

if (!$isActive) {
    closeDBConnection($conn);
    echo json_encode(['success' => true, 'message' => 'Partner link opened but membership is not active. No commission will be earned.', 'data' => ['active' => false]]);
    exit;
}

$ip = $_SERVER['REMOTE_ADDR'] ?? null;
$sessionId = session_id() ?: (isset($_COOKIE['session_id']) ? $_COOKIE['session_id'] : null);
$ua = isset($_SERVER['HTTP_USER_AGENT']) ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null;

$ins = $conn->prepare("INSERT INTO creator_clicks (creator_id, membership_id, product_id, visitor_ip, visitor_session, user_agent) VALUES (?, ?, ?, ?, ?, ?)");
$ins->bind_param("iiisss", $creatorId, $membershipId, $productId, $ip, $sessionId, $ua);
$ins->execute();
$ins->close();

$conn->query("UPDATE creator_profiles SET total_clicks = total_clicks + 1 WHERE id = $creatorId");

closeDBConnection($conn);

$expiry = time() + (28 * 86400);
setcookie('partner_creator_id', (string) $creatorId, $expiry, '/', '', false, true);
setcookie('partner_code', $code, $expiry, '/', '', false, true);
setcookie('partner_membership_id', (string) $membershipId, $expiry, '/', '', false, true);
setcookie('partner_expiry', (string) $expiry, $expiry, '/', '', false, true);

echo json_encode([
    'success' => true,
    'message' => 'Partner referral tracked',
    'data' => [
        'creator_id' => $creatorId,
        'code' => $code,
        'active' => true
    ]
]);
