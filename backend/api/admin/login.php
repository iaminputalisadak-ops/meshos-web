<?php
/**
 * Admin Login API Endpoint
 */

// CRITICAL: Turn off all output buffering and error display
// This ensures we always return JSON, never HTML
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start(); // Start output buffering to catch any accidental output

// Set headers first - MUST be before any output
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../../config/database.php';
require_once '../../config/cors.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize connection as null
$conn = null;

try {
    // Check if database connection can be established
    try {
        $conn = getDBConnection();
    } catch (Exception $dbError) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Database connection failed. Please make sure MySQL is running and database is set up.',
            'error' => $dbError->getMessage(),
            'setup_needed' => true,
            'setup_url' => '../database/setup_database.php'
        ]);
        exit;
    }
    
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
    // Clear any output that might have been sent
    ob_clean();
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage(),
        'error' => $e->getMessage(),
        'setup_needed' => (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'table') !== false),
        'setup_url' => '../database/setup_database.php'
    ]);
    if ($conn) {
        closeDBConnection($conn);
    }
    ob_end_flush();
    exit;
} catch (Error $e) {
    // Clear any output that might have been sent
    ob_clean();
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'error' => $e->getMessage(),
        'setup_needed' => true,
        'setup_url' => '../database/setup_database.php'
    ]);
    if ($conn) {
        closeDBConnection($conn);
    }
    ob_end_flush();
    exit;
} finally {
    // Clean any accidental output
    ob_clean();
    
    if ($conn) {
        closeDBConnection($conn);
    }
    
    // End output buffering and send response
    ob_end_flush();
}

/**
 * Handle admin login
 */
function handleAdminLogin($conn) {
    try {
        // Get JSON input
        $json = file_get_contents('php://input');
        if (empty($json)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'No data received'
            ]);
            return;
        }
        
        $data = json_decode($json, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid JSON data: ' . json_last_error_msg()
            ]);
            return;
        }
        
        if (!isset($data['username']) || !isset($data['password'])) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Username and password are required'
            ]);
            return;
        }
        
        $username = trim($data['username']);
        $password = $data['password'];
        
        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Username and password cannot be empty'
            ]);
            return;
        }
        
        // Check if admin_users table exists
        $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
        if ($tableCheck->num_rows === 0) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Database not set up. Please run database setup first.',
                'setup_url' => '../database/setup_database.php'
            ]);
            return;
        }
        
        // Get admin user
        $stmt = $conn->prepare("
            SELECT id, username, email, password, full_name, role, status 
            FROM admin_users 
            WHERE username = ? AND status = 'active'
        ");
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
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
        
        // Update last login (if column exists)
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
        
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Set session variables
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_email'] = $admin['email'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_logged_in'] = true;
        
        // Set session cookie parameters for better security
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), session_id(), [
                'expires' => time() + 86400, // 24 hours
                'path' => $params["path"],
                'domain' => $params["domain"],
                'secure' => $params["secure"],
                'httponly' => $params["httponly"],
                'samesite' => 'Lax'
            ]);
        }
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'id' => $admin['id'],
                'username' => $admin['username'],
                'email' => $admin['email'],
                'full_name' => $admin['full_name'],
                'role' => $admin['role']
            ],
            'session_id' => session_id()
        ]);
        
        $stmt->close();
        
    } catch (Exception $e) {
        // Clear any output
        ob_clean();
        
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Login error: ' . $e->getMessage(),
            'error' => $e->getMessage(),
            'setup_needed' => (strpos($e->getMessage(), 'database') !== false || strpos($e->getMessage(), 'table') !== false),
            'setup_url' => '../database/setup_database.php'
        ]);
    }
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


