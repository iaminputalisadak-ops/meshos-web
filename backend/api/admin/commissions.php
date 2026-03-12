<?php
/**
 * Admin: List commissions, filter by status
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

session_start();
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['admin_logged_in'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$conn = getDBConnection();
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$sql = "
    SELECT c.*, pp.code as promoter_code, u.name as promoter_name, u.email as promoter_email
    FROM commissions c
    JOIN promoter_profiles pp ON c.promoter_id = pp.id
    JOIN users u ON pp.user_id = u.id
";
if ($status !== '') {
    $sql .= " WHERE c.status = '" . $conn->real_escape_string($status) . "'";
}
$sql .= " ORDER BY c.created_at DESC LIMIT 200";

$res = $conn->query($sql);
$list = [];
while ($row = $res->fetch_assoc()) {
    $list[] = $row;
}

$totals = $conn->query("SELECT status, COUNT(*) as cnt, SUM(commission_amount) as total FROM commissions GROUP BY status")->fetch_all(MYSQLI_ASSOC);
closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'data' => $list,
    'totals' => $totals
]);
