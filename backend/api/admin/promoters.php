<?php
/**
 * Admin: List promoters, approve/reject
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
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $stmt = $conn->query("
        SELECT pp.*, u.name, u.email, u.phone, u.created_at as user_created
        FROM promoter_profiles pp
        JOIN users u ON pp.user_id = u.id
        ORDER BY pp.created_at DESC
    ");
    $list = [];
    while ($row = $stmt->fetch_assoc()) {
        $list[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $list]);
    exit;
}

if ($method === 'PATCH' || $method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true) ?: [];
    $id = (int) ($input['promoter_id'] ?? $input['id'] ?? 0);
    $action = trim($input['action'] ?? $input['status'] ?? '');
    if (!$id || !in_array($action, ['approve', 'reject', 'suspend'], true)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'promoter_id and action (approve|reject|suspend) required']);
        exit;
    }
    $status = $action === 'approve' ? 'approved' : ($action === 'reject' ? 'rejected' : 'suspended');
    $approvedAt = $action === 'approve' ? ', approved_at = NOW()' : '';
    $conn->query("UPDATE promoter_profiles SET status = '$status' $approvedAt WHERE id = $id");
    echo json_encode(['success' => true, 'message' => "Promoter $action", 'data' => ['promoter_id' => $id, 'status' => $status]]);
    exit;
}

http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Method not allowed']);
closeDBConnection($conn);
