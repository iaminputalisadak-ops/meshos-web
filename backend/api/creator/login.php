<?php
/**
 * Creator Partner Login
 * POST: email, password
 * Checks user + creator_profile (approved) + active membership
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/cors.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$email    = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

try {
    $conn = getDBConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

$stmt = $conn->prepare("
    SELECT u.id, u.name, u.email, u.password, u.phone,
           cp.id as creator_id, cp.creator_code, cp.approval_status, cp.active_status,
           cp.commission_rate, cp.wallet_balance
    FROM users u
    LEFT JOIN creator_profiles cp ON cp.user_id = u.id
    WHERE u.email = ?
");
$stmt->bind_param("s", $email);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows === 0) {
    $stmt->close();
    closeDBConnection($conn);
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

$row = $r->fetch_assoc();
$stmt->close();

if (!password_verify($password, $row['password'])) {
    closeDBConnection($conn);
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

if (empty($row['creator_id'])) {
    closeDBConnection($conn);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not a Creator Partner account. Register first.']);
    exit;
}

if ($row['approval_status'] === 'pending') {
    closeDBConnection($conn);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your Creator Partner account is pending admin approval.']);
    exit;
}

if ($row['approval_status'] === 'rejected') {
    closeDBConnection($conn);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your Creator Partner account was rejected.']);
    exit;
}

$creatorId = (int) $row['creator_id'];
$membership = null;
$stmt2 = $conn->prepare("
    SELECT id, starts_at, expires_at, payment_status, admin_approval, renewal_number
    FROM creator_memberships
    WHERE creator_id = ? AND admin_approval = 'approved' AND payment_status = 'paid'
    ORDER BY expires_at DESC LIMIT 1
");
$stmt2->bind_param("i", $creatorId);
$stmt2->execute();
$mRes = $stmt2->get_result();
if ($mRes->num_rows > 0) {
    $membership = $mRes->fetch_assoc();
}
$stmt2->close();

$isActive = false;
$daysLeft = 0;
if ($membership && $membership['expires_at']) {
    $expiry = strtotime($membership['expires_at']);
    $now = time();
    if ($expiry > $now) {
        $isActive = true;
        $daysLeft = (int) ceil(($expiry - $now) / 86400);
    }
}

$newActiveStatus = $isActive ? 'active' : 'expired';
if ($row['active_status'] !== $newActiveStatus) {
    $conn->query("UPDATE creator_profiles SET active_status = '$newActiveStatus' WHERE id = $creatorId");
}

closeDBConnection($conn);

$_SESSION['user_id']          = $row['id'];
$_SESSION['user_email']       = $row['email'];
$_SESSION['user_name']        = $row['name'];
$_SESSION['creator_id']       = $creatorId;
$_SESSION['creator_code']     = $row['creator_code'];
$_SESSION['creator_logged_in'] = true;

echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'data' => [
        'user_id'          => (int) $row['id'],
        'name'             => $row['name'],
        'email'            => $row['email'],
        'creator_id'       => $creatorId,
        'creator_code'     => $row['creator_code'],
        'commission_rate'  => (float) $row['commission_rate'],
        'wallet_balance'   => (float) $row['wallet_balance'],
        'membership_active' => $isActive,
        'days_left'        => $daysLeft,
        'active_status'    => $newActiveStatus
    ]
]);
