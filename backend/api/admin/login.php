<?php
/**
 * Admin Login API Endpoint
 */

require_once '../../config/database.php';
require_once '../../config/cors.php';
require_once '../../config/error_handler.php';

// Start session
session_start();

$conn = getDBConnection();

try {
    $method = $_SERVER['REQUEST_METHOD'];
    
    if ($method === 'POST') {
        handleAdminLogin($conn);
    } elseif ($method === 'GET' && isset($_GET['logout'])) {
        handleAdminLogout();
    } else {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'message' => 'Method not allowed'
        ]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error',
        'error' => $e->getMessage()
    ]);
} finally {
    closeDBConnection($conn);
}

/**
 * Handle admin login
 */
function handleAdminLogin($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['username']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Username and password are required'
        ]);
        return;
    }
    
    $username = $data['username'];
    $password = $data['password'];
    
    // Get admin user
    $stmt = $conn->prepare("
        SELECT id, username, email, password, full_name, role, status 
        FROM admin_users 
        WHERE username = ? AND status = 'active'
    ");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
        $stmt->close();
        return;
    }
    
    $admin = $result->fetch_assoc();
    
    // Verify password
    if (!password_verify($password, $admin['password'])) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Invalid username or password'
        ]);
        $stmt->close();
        return;
    }
    
    // Update last login
    $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
    $updateStmt->bind_param("i", $admin['id']);
    $updateStmt->execute();
    $updateStmt->close();
    
    // Set session
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_logged_in'] = true;
    
    echo json_encode([
        'success' => true,
        'message' => 'Login successful',
        'data' => [
            'id' => $admin['id'],
            'username' => $admin['username'],
            'email' => $admin['email'],
            'full_name' => $admin['full_name'],
            'role' => $admin['role']
        ]
    ]);
    
    $stmt->close();
}

/**
 * Handle admin logout
 */
function handleAdminLogout() {
    session_start();
    session_unset();
    session_destroy();
    
    echo json_encode([
        'success' => true,
        'message' => 'Logged out successfully'
    ]);
}
?>


