<?php
/**
 * Creator Partner Dashboard
 * GET: returns membership info, stats, commissions, products with partner links
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
    echo json_encode(['success' => false, 'message' => 'Not logged in as Creator Partner']);
    exit;
}

$conn = getDBConnection();
$cid  = (int) $_SESSION['creator_id'];
$code = $_SESSION['creator_code'];

$baseUrl = isset($_SERVER['HTTP_ORIGIN']) ? rtrim($_SERVER['HTTP_ORIGIN'], '/') : 'http://localhost:3000';

$stmt = $conn->prepare("
    SELECT cp.*, u.name, u.email, u.phone
    FROM creator_profiles cp
    JOIN users u ON cp.user_id = u.id
    WHERE cp.id = ?
");
$stmt->bind_param("i", $cid);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile) {
    closeDBConnection($conn);
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Creator profile not found']);
    exit;
}

$stmt2 = $conn->prepare("
    SELECT id, starts_at, expires_at, payment_status, admin_approval, duration_days, renewal_number, created_at
    FROM creator_memberships
    WHERE creator_id = ? AND admin_approval = 'approved' AND payment_status = 'paid'
    ORDER BY expires_at DESC LIMIT 1
");
$stmt2->bind_param("i", $cid);
$stmt2->execute();
$membership = $stmt2->get_result()->fetch_assoc();
$stmt2->close();

$isActive = false;
$daysLeft = 0;
$expiresAt = null;
$startsAt = null;
if ($membership && $membership['expires_at']) {
    $expiry = strtotime($membership['expires_at']);
    $now = time();
    $expiresAt = $membership['expires_at'];
    $startsAt  = $membership['starts_at'];
    if ($expiry > $now) {
        $isActive = true;
        $daysLeft = (int) ceil(($expiry - $now) / 86400);
    }
}

$newActiveStatus = $isActive ? 'active' : 'expired';
if ($profile['active_status'] !== $newActiveStatus) {
    $conn->query("UPDATE creator_profiles SET active_status = '$newActiveStatus' WHERE id = $cid");
}

$pendingMembership = null;
$stmtPending = $conn->prepare("
    SELECT id, payment_amount, payment_status, admin_approval, created_at
    FROM creator_memberships
    WHERE creator_id = ? AND (admin_approval = 'pending' OR payment_status = 'pending')
    ORDER BY created_at DESC LIMIT 1
");
$stmtPending->bind_param("i", $cid);
$stmtPending->execute();
$pm = $stmtPending->get_result()->fetch_assoc();
$stmtPending->close();
if ($pm) {
    $pendingMembership = $pm;
}

$stats = [
    'total_clicks'  => (int) $profile['total_clicks'],
    'total_orders'  => (int) $profile['total_orders'],
    'total_sales'   => (float) $profile['total_sales'],
    'total_earned'  => (float) $profile['total_earned'],
    'total_paid'    => (float) $profile['total_paid'],
    'wallet_balance' => (float) $profile['wallet_balance'],
];

$stmtPendComm = $conn->prepare("SELECT COALESCE(SUM(commission_amount), 0) as pending FROM creator_commissions WHERE creator_id = ? AND status = 'pending'");
$stmtPendComm->bind_param("i", $cid);
$stmtPendComm->execute();
$stats['pending_commission'] = (float) $stmtPendComm->get_result()->fetch_assoc()['pending'];
$stmtPendComm->close();

$stmtApprComm = $conn->prepare("SELECT COALESCE(SUM(commission_amount), 0) as approved FROM creator_commissions WHERE creator_id = ? AND status = 'approved'");
$stmtApprComm->bind_param("i", $cid);
$stmtApprComm->execute();
$stats['approved_commission'] = (float) $stmtApprComm->get_result()->fetch_assoc()['approved'];
$stmtApprComm->close();

$stmtComm = $conn->prepare("
    SELECT cc.id, cc.order_id, cc.sale_amount, cc.commission_rate, cc.commission_amount, cc.status, cc.created_at
    FROM creator_commissions cc
    WHERE cc.creator_id = ?
    ORDER BY cc.created_at DESC LIMIT 20
");
$stmtComm->bind_param("i", $cid);
$stmtComm->execute();
$res = $stmtComm->get_result();
$commissions = [];
while ($row = $res->fetch_assoc()) {
    $commissions[] = $row;
}
$stmtComm->close();

$products = [];
if ($isActive) {
    $res2 = $conn->query("SELECT id, name, price, image FROM products WHERE in_stock = 1 ORDER BY name LIMIT 100");
    while ($row = $res2->fetch_assoc()) {
        $row['partner_link'] = $baseUrl . '/product/' . $row['id'] . '?partner=' . urlencode($code);
        $products[] = $row;
    }
}

$walletStmt = $conn->prepare("
    SELECT id, type, source, amount, balance_after, description, created_at
    FROM creator_wallet_transactions
    WHERE creator_id = ?
    ORDER BY created_at DESC LIMIT 20
");
$walletStmt->bind_param("i", $cid);
$walletStmt->execute();
$wRes = $walletStmt->get_result();
$walletHistory = [];
while ($row = $wRes->fetch_assoc()) {
    $walletHistory[] = $row;
}
$walletStmt->close();

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => [
        'creator_code'    => $code,
        'name'            => $profile['name'],
        'email'           => $profile['email'],
        'commission_rate'  => (float) $profile['commission_rate'],
        'membership' => [
            'is_active'   => $isActive,
            'days_left'   => $daysLeft,
            'starts_at'   => $startsAt,
            'expires_at'  => $expiresAt,
            'renewal_number' => $membership ? (int) $membership['renewal_number'] : 0,
        ],
        'pending_membership' => $pendingMembership,
        'stats'           => $stats,
        'commissions'     => $commissions,
        'products'        => $products,
        'wallet_history'  => $walletHistory,
        'base_url'        => $baseUrl
    ]
]);
