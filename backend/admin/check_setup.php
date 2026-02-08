<?php
/**
 * Quick Database Setup Check
 */

// Set headers first
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// Handle errors gracefully
error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require_once '../config/database.php';
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database config not found: ' . $e->getMessage(),
        'setup_url' => '../setup.php'
    ]);
    exit;
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        echo json_encode([
            'success' => false,
            'message' => 'Cannot connect to MySQL. Make sure MySQL is running in XAMPP.',
            'error' => $conn->connect_error,
            'setup_url' => '../setup.php'
        ]);
        exit;
    }
    
    // Check if database exists
    $dbCheck = $conn->query("SHOW DATABASES LIKE '" . DB_NAME . "'");
    if ($dbCheck->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Database does not exist. Please run setup.',
            'setup_needed' => true,
            'setup_url' => '../setup.php'
        ]);
        exit;
    }
    
    $conn->select_db(DB_NAME);
    
    // Check if admin_users table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($tableCheck->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Tables not set up. Please run setup.',
            'setup_needed' => true,
            'setup_url' => '../setup.php'
        ]);
        exit;
    }
    
    // Check if admin user exists
    $userCheck = $conn->query("SELECT COUNT(*) as count FROM admin_users");
    if ($userCheck === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Error checking admin users.',
            'setup_url' => '../setup.php'
        ]);
        exit;
    }
    
    $userCount = $userCheck->fetch_assoc()['count'];
    
    if ($userCount === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'No admin users found. Please run setup.',
            'setup_needed' => true,
            'setup_url' => '../setup.php'
        ]);
        exit;
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Database is set up correctly',
        'admin_users' => $userCount
    ]);
    
    $conn->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'error' => $e->getMessage(),
        'setup_url' => '../setup.php'
    ]);
} catch (Error $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $e->getMessage(),
        'error' => $e->getMessage(),
        'setup_url' => '../setup.php'
    ]);
}
?>

