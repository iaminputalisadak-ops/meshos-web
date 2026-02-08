<?php
/**
 * Direct Login Handler - Self-contained in admin folder
 * This bypasses all path issues and handles login directly
 */

// Start output buffering immediately - CRITICAL
ob_start();

// Set error handling - NO HTML ERRORS
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set headers FIRST
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    http_response_code(200);
    ob_end_flush();
    exit;
}

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_clean();
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    ob_end_flush();
    exit;
}

// Function to send JSON response
function sendJSON($success, $message, $data = null, $httpCode = 200) {
    ob_clean();
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    ob_end_flush();
    exit;
}

try {
    // Get JSON input
    $json = file_get_contents('php://input');
    if (empty($json)) {
        sendJSON(false, 'No data received', null, 400);
    }
    
    $data = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        sendJSON(false, 'Invalid JSON: ' . json_last_error_msg(), null, 400);
    }
    
    if (!isset($data['username']) || !isset($data['password'])) {
        sendJSON(false, 'Username and password are required', null, 400);
    }
    
    $username = trim($data['username']);
    $password = $data['password'];
    
    if (empty($username) || empty($password)) {
        sendJSON(false, 'Username and password cannot be empty', null, 400);
    }
    
    // Connect to database - handle path correctly
    $dbConfigPath = __DIR__ . '/../config/database.php';
    if (!file_exists($dbConfigPath)) {
        sendJSON(false, 'Database config not found', ['setup_url' => '../database/setup_database.php'], 500);
    }
    
    require_once $dbConfigPath;
    
    try {
        $conn = getDBConnection();
    } catch (Exception $e) {
        sendJSON(false, 'Database connection failed. Make sure MySQL is running in XAMPP.', [
            'error' => $e->getMessage(),
            'setup_url' => '../database/setup_database.php'
        ], 500);
    }
    
    // Check if admin_users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($tableCheck === false || $tableCheck->num_rows === 0) {
        $conn->close();
        sendJSON(false, 'Database not set up. Please run database setup first.', [
            'setup_url' => '../database/setup_database.php',
            'setup_needed' => true
        ], 500);
    }
    
    // Get admin user
    $stmt = $conn->prepare("SELECT id, username, email, password, full_name, role, status FROM admin_users WHERE username = ? AND status = 'active'");
    
    if (!$stmt) {
        $conn->close();
        sendJSON(false, 'Database query failed: ' . $conn->error, null, 500);
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        $conn->close();
        sendJSON(false, 'Invalid username or password', null, 401);
    }
    
    $admin = $result->fetch_assoc();
    $stmt->close();
    
    // Verify password
    if (!password_verify($password, $admin['password'])) {
        $conn->close();
        sendJSON(false, 'Invalid username or password', null, 401);
    }
    
    // Update last login
    try {
        $updateStmt = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        if ($updateStmt) {
            $updateStmt->bind_param("i", $admin['id']);
            $updateStmt->execute();
            $updateStmt->close();
        }
    } catch (Exception $e) {
        // Ignore if last_login column doesn't exist
    }
    
    $conn->close();
    
    // Start session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Set session variables
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];
    $_SESSION['admin_email'] = $admin['email'];
    $_SESSION['admin_role'] = $admin['role'];
    $_SESSION['admin_logged_in'] = true;
    
    // Success response
    sendJSON(true, 'Login successful', [
        'id' => $admin['id'],
        'username' => $admin['username'],
        'email' => $admin['email'],
        'full_name' => $admin['full_name'],
        'role' => $admin['role']
    ], 200);
    
} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
    ob_end_flush();
    exit;
} catch (Error $e) {
    ob_clean();
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'error' => $e->getMessage()
    ]);
    ob_end_flush();
    exit;
}
