<?php
/**
 * Get current partner referral from cookie (for checkout to attach creator)
 * GET: returns { success, data: { creator_id, code, membership_id } or null }
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

header('Content-Type: application/json; charset=utf-8');

$creatorId    = isset($_COOKIE['partner_creator_id']) ? (int) $_COOKIE['partner_creator_id'] : 0;
$code         = isset($_COOKIE['partner_code']) ? trim($_COOKIE['partner_code']) : '';
$membershipId = isset($_COOKIE['partner_membership_id']) ? (int) $_COOKIE['partner_membership_id'] : 0;
$expiry       = isset($_COOKIE['partner_expiry']) ? (int) $_COOKIE['partner_expiry'] : 0;

if ($creatorId < 1 || $expiry < time() || $code === '') {
    echo json_encode(['success' => true, 'data' => null]);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("
    SELECT cp.id, cp.creator_code, cp.active_status
    FROM creator_profiles cp
    WHERE cp.id = ? AND cp.creator_code = ? AND cp.approval_status = 'approved'
");
$stmt->bind_param("is", $creatorId, $code);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    echo json_encode(['success' => true, 'data' => null]);
    exit;
}
$creator = $r->fetch_assoc();
$stmt->close();

$isActive = false;
if ($membershipId > 0) {
    $memStmt = $conn->prepare("
        SELECT id, expires_at FROM creator_memberships
        WHERE id = ? AND creator_id = ? AND admin_approval = 'approved' AND payment_status = 'paid'
    ");
    $memStmt->bind_param("ii", $membershipId, $creatorId);
    $memStmt->execute();
    $mem = $memStmt->get_result()->fetch_assoc();
    $memStmt->close();
    if ($mem && $mem['expires_at'] && strtotime($mem['expires_at']) > time()) {
        $isActive = true;
    }
}

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => [
        'creator_id'    => (int) $creator['id'],
        'code'          => $creator['creator_code'],
        'membership_id' => $membershipId,
        'is_active'     => $isActive
    ]
]);
