<?php
/**
 * Creator Partner Registration
 * POST: name, email, phone, password, payment_reference
 * Creates user + creator_profile + pending membership (₹500)
 */
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../../config/cors.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$name     = trim($input['name'] ?? '');
$email    = trim($input['email'] ?? '');
$phone    = trim($input['phone'] ?? '');
$password = $input['password'] ?? '';
$payRef   = trim($input['payment_reference'] ?? '');

if (!$name || !$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Name, email and password are required']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit;
}

try {
    $conn = getDBConnection();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error. Make sure MySQL is running.']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT cp.id FROM creator_profiles cp JOIN users u ON cp.user_id = u.id WHERE u.email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Already registered as a Creator Partner']);
        exit;
    }
    $stmt->close();

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

    do {
        $code = 'CP' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
        $chk = $conn->prepare("SELECT id FROM creator_profiles WHERE creator_code = ?");
        $chk->bind_param("s", $code);
        $chk->execute();
        $exists = $chk->get_result()->num_rows > 0;
        $chk->close();
    } while ($exists);

    $stmt = $conn->prepare("INSERT INTO creator_profiles (user_id, creator_code, approval_status, active_status) VALUES (?, ?, 'pending', 'inactive')");
    $stmt->bind_param("is", $userId, $code);
    if (!$stmt->execute()) {
        $stmt->close();
        closeDBConnection($conn);
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Creator profile creation failed']);
        exit;
    }
    $creatorId = $conn->insert_id;
    $stmt->close();

    $paymentStatus = $payRef ? 'paid' : 'pending';
    $stmt = $conn->prepare("INSERT INTO creator_memberships (creator_id, payment_amount, payment_status, payment_reference, admin_approval, duration_days, renewal_number) VALUES (?, 500.00, ?, ?, 'pending', 28, 1)");
    $stmt->bind_param("iss", $creatorId, $paymentStatus, $payRef);
    $stmt->execute();
    $stmt->close();

    closeDBConnection($conn);

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! Pay ₹500 membership fee and wait for admin approval. Your 28-day active period starts after approval.',
        'data' => [
            'user_id'      => (int) $userId,
            'creator_code' => $code,
            'status'        => 'pending',
            'membership_fee' => 500
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error. Make sure MySQL is running and creator tables exist.']);
} catch (Throwable $e) {
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
}
