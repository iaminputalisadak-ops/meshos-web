<?php
/**
 * Current Creator Partner info (must be logged in)
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/cors.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();

if (empty($_SESSION['creator_logged_in']) || empty($_SESSION['creator_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$conn = getDBConnection();
$cid = (int) $_SESSION['creator_id'];

$stmt = $conn->prepare("
    SELECT cp.*, u.name, u.email, u.phone
    FROM creator_profiles cp
    JOIN users u ON cp.user_id = u.id
    WHERE cp.id = ?
");
$stmt->bind_param("i", $cid);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Creator not found']);
    exit;
}
$profile = $r->fetch_assoc();
$stmt->close();

$stmt2 = $conn->prepare("
    SELECT id, starts_at, expires_at, admin_approval, payment_status
    FROM creator_memberships
    WHERE creator_id = ? AND admin_approval = 'approved' AND payment_status = 'paid'
    ORDER BY expires_at DESC LIMIT 1
");
$stmt2->bind_param("i", $cid);
$stmt2->execute();
$mem = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

$isActive = false;
$daysLeft = 0;
if ($mem && $mem['expires_at'] && strtotime($mem['expires_at']) > time()) {
    $isActive = true;
    $daysLeft = (int) ceil((strtotime($mem['expires_at']) - time()) / 86400);
}

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => [
        'creator_id'       => (int) $profile['id'],
        'creator_code'     => $profile['creator_code'],
        'name'             => $profile['name'],
        'email'            => $profile['email'],
        'phone'            => $profile['phone'],
        'approval_status'  => $profile['approval_status'],
        'active_status'    => $isActive ? 'active' : 'expired',
        'commission_rate'  => (float) $profile['commission_rate'],
        'wallet_balance'   => (float) $profile['wallet_balance'],
        'membership_active' => $isActive,
        'days_left'        => $daysLeft
    ]
]);
