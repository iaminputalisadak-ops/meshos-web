<?php
/**
 * Creator Partner Membership Renewal
 * POST: payment_reference (optional)
 * Creates a new membership record for renewal
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

if (empty($_SESSION['creator_logged_in']) || empty($_SESSION['creator_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$payRef = trim($input['payment_reference'] ?? '');

$conn = getDBConnection();
$cid = (int) $_SESSION['creator_id'];

$stmt = $conn->prepare("SELECT id, approval_status FROM creator_profiles WHERE id = ?");
$stmt->bind_param("i", $cid);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$profile || $profile['approval_status'] === 'rejected') {
    closeDBConnection($conn);
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Your account is not eligible for renewal']);
    exit;
}

$stmt2 = $conn->prepare("
    SELECT id FROM creator_memberships
    WHERE creator_id = ? AND (admin_approval = 'pending' OR payment_status = 'pending')
    LIMIT 1
");
$stmt2->bind_param("i", $cid);
$stmt2->execute();
if ($stmt2->get_result()->num_rows > 0) {
    $stmt2->close();
    closeDBConnection($conn);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You already have a pending membership renewal. Wait for admin approval.']);
    exit;
}
$stmt2->close();

$maxRenewal = 1;
$stmtMax = $conn->prepare("SELECT MAX(renewal_number) as max_r FROM creator_memberships WHERE creator_id = ?");
$stmtMax->bind_param("i", $cid);
$stmtMax->execute();
$maxRow = $stmtMax->get_result()->fetch_assoc();
$stmtMax->close();
if ($maxRow && $maxRow['max_r']) {
    $maxRenewal = (int) $maxRow['max_r'] + 1;
}

$paymentStatus = $payRef ? 'paid' : 'pending';
$stmt3 = $conn->prepare("INSERT INTO creator_memberships (creator_id, payment_amount, payment_status, payment_reference, admin_approval, duration_days, renewal_number) VALUES (?, 500.00, ?, ?, 'pending', 28, ?)");
$stmt3->bind_param("issi", $cid, $paymentStatus, $payRef, $maxRenewal);
$stmt3->execute();
$stmt3->close();

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'message' => 'Renewal request submitted. Pay ₹500 and wait for admin approval. Your new 28-day period starts after approval.',
    'data' => [
        'renewal_number' => $maxRenewal,
        'membership_fee' => 500
    ]
]);
