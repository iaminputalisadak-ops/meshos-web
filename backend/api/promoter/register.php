<?php
/**
 * Promoter Registration API
 * POST: name, email, phone, password => creates user + promoter_profile (pending)
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/cors.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';

if (!$name || !$email || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name, email and password are required']);
    exit;
}

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

$conn = getDBConnection();

// Check if user already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows > 0) {
    $stmt->close();
    closeDBConnection($conn);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}
$stmt->close();

// Check if already a promoter
$stmt = $conn->prepare("SELECT id FROM promoter_profiles pp JOIN users u ON pp.user_id = u.id WHERE u.email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$r = $stmt->get_result();
if ($r->num_rows > 0) {
    $stmt->close();
    closeDBConnection($conn);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'This email is already registered as a promoter']);
    exit;
}
$stmt->close();

// Create user
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $name, $email, $hash, $phone);
if (!$stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
    exit;
}
$userId = $conn->insert_id;
$stmt->close();

// Generate unique promoter code (e.g. PROMO + 6 alphanumeric)
do {
    $code = 'P' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    $chk = $conn->prepare("SELECT id FROM promoter_profiles WHERE code = ?");
    $chk->bind_param("s", $code);
    $chk->execute();
    $exists = $chk->get_result()->num_rows > 0;
    $chk->close();
} while ($exists);

$stmt = $conn->prepare("INSERT INTO promoter_profiles (user_id, code, status) VALUES (?, ?, 'pending')");
$stmt->bind_param("is", $userId, $code);
if (!$stmt->execute()) {
    $stmt->close();
    closeDBConnection($conn);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Promoter profile creation failed']);
    exit;
}
$stmt->close();
closeDBConnection($conn);

echo json_encode([
    'success' => true,
    'message' => 'Registration successful. Your account is pending admin approval. You will be able to login once approved.',
    'data' => [
        'user_id' => (int) $userId,
        'promoter_code' => $code,
        'status' => 'pending'
    ]
]);
