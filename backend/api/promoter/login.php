<?php
/**
 * Promoter Login API
 * POST: email, password => session + promoter info (only if promoter approved)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

session_start();

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (!$email || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email and password required']);
    exit;
}

$conn = getDBConnection();

$stmt = $conn->prepare("SELECT u.id, u.name, u.email, u.password, u.phone, pp.id as promoter_id, pp.code, pp.status as promoter_status FROM users u LEFT JOIN promoter_profiles pp ON pp.user_id = u.id WHERE u.email = ?");
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
closeDBConnection($conn);

if (!password_verify($password, $row['password'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
    exit;
}

if (empty($row['promoter_id']) || $row['promoter_status'] !== 'approved') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => $row['promoter_status'] === 'pending' ? 'Your promoter account is pending approval.' : ($row['promoter_status'] === 'rejected' ? 'Your promoter account was rejected.' : 'Not a promoter account.')
    ]);
    exit;
}

$_SESSION['user_id'] = $row['id'];
$_SESSION['user_email'] = $row['email'];
$_SESSION['user_name'] = $row['name'];
$_SESSION['promoter_id'] = $row['promoter_id'];
$_SESSION['promoter_code'] = $row['code'];
$_SESSION['promoter_logged_in'] = true;

unset($row['password']);
echo json_encode([
    'success' => true,
    'message' => 'Login successful',
    'data' => [
        'user_id' => (int) $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'promoter_id' => (int) $row['promoter_id'],
        'promoter_code' => $row['code']
    ]
]);
