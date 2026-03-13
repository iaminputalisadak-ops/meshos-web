<?php
/**
 * Admin: Creator Commissions
 * GET: list commissions with optional status filter
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDBConnection();

$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : '';
$where = '';
if (in_array($statusFilter, ['pending', 'approved', 'rejected', 'paid', 'reversed'])) {
    $where = "WHERE cc.status = '$statusFilter'";
}

$result = $conn->query("
    SELECT cc.*, cp.creator_code, u.name as creator_name, u.email as creator_email
    FROM creator_commissions cc
    JOIN creator_profiles cp ON cc.creator_id = cp.id
    JOIN users u ON cp.user_id = u.id
    $where
    ORDER BY cc.created_at DESC
    LIMIT 200
");

$commissions = [];
while ($row = $result->fetch_assoc()) {
    $commissions[] = $row;
}

$totals = [];
$totalsResult = $conn->query("
    SELECT status, COUNT(*) as count, COALESCE(SUM(commission_amount), 0) as total
    FROM creator_commissions GROUP BY status
");
while ($row = $totalsResult->fetch_assoc()) {
    $totals[$row['status']] = ['count' => (int) $row['count'], 'total' => (float) $row['total']];
}

$membershipRevenue = $conn->query("
    SELECT COUNT(*) as count, COALESCE(SUM(payment_amount), 0) as total
    FROM creator_memberships WHERE payment_status = 'paid'
")->fetch_assoc();

closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => $commissions,
    'totals' => $totals,
    'membership_revenue' => [
        'total_memberships_paid' => (int) $membershipRevenue['count'],
        'total_revenue' => (float) $membershipRevenue['total']
    ]
]);
