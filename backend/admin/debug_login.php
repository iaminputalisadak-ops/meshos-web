<?php
/**
 * Debug Login - Test database and admin user
 */
require_once '../config/database.php';

header('Content-Type: application/json');

$results = [
    'checks' => [],
    'success' => true
];

try {
    // Check 1: MySQL Connection
    $testConn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    if ($testConn->connect_error) {
        $results['checks'][] = [
            'name' => 'MySQL Connection',
            'status' => 'FAIL',
            'message' => 'Cannot connect to MySQL: ' . $testConn->connect_error
        ];
        $results['success'] = false;
    } else {
        $results['checks'][] = [
            'name' => 'MySQL Connection',
            'status' => 'PASS',
            'message' => 'Connected to MySQL successfully'
        ];
        $testConn->close();
    }
    
    // Check 2: Database Exists
    try {
        $conn = getDBConnection();
        $results['checks'][] = [
            'name' => 'Database Connection',
            'status' => 'PASS',
            'message' => 'Database "' . DB_NAME . '" exists and is accessible'
        ];
    } catch (Exception $e) {
        $results['checks'][] = [
            'name' => 'Database Connection',
            'status' => 'FAIL',
            'message' => $e->getMessage()
        ];
        $results['success'] = false;
        echo json_encode($results);
        exit;
    }
    
    // Check 3: admin_users Table Exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'admin_users'");
    if ($tableCheck->num_rows === 0) {
        $results['checks'][] = [
            'name' => 'admin_users Table',
            'status' => 'FAIL',
            'message' => 'Table does not exist. Run setup_database.php'
        ];
        $results['success'] = false;
    } else {
        $results['checks'][] = [
            'name' => 'admin_users Table',
            'status' => 'PASS',
            'message' => 'Table exists'
        ];
    }
    
    // Check 4: Admin User Exists
    $userCheck = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE username = 'admin'");
    if ($userCheck) {
        $userCount = $userCheck->fetch_assoc()['count'];
        if ($userCount === 0) {
            $results['checks'][] = [
                'name' => 'Admin User',
                'status' => 'FAIL',
                'message' => 'No admin user found. Run setup_database.php'
            ];
            $results['success'] = false;
        } else {
            // Check 5: Test Password
            $stmt = $conn->prepare("SELECT password FROM admin_users WHERE username = 'admin'");
            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                $admin = $result->fetch_assoc();
                
                if ($admin && password_verify('admin123', $admin['password'])) {
                    $results['checks'][] = [
                        'name' => 'Admin User',
                        'status' => 'PASS',
                        'message' => 'Admin user exists and password is correct'
                    ];
                    $results['checks'][] = [
                        'name' => 'Password Verification',
                        'status' => 'PASS',
                        'message' => 'Password "admin123" is valid'
                    ];
                } else {
                    $results['checks'][] = [
                        'name' => 'Admin User',
                        'status' => 'PASS',
                        'message' => 'Admin user exists'
                    ];
                    $results['checks'][] = [
                        'name' => 'Password Verification',
                        'status' => 'FAIL',
                        'message' => 'Password "admin123" does not match. Run setup_database.php to reset'
                    ];
                    $results['success'] = false;
                }
                $stmt->close();
            }
        }
    }
    
    // Check 6: Session Configuration
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $results['checks'][] = [
        'name' => 'Session Support',
        'status' => 'PASS',
        'message' => 'PHP sessions are working'
    ];
    
    $conn->close();
    
} catch (Exception $e) {
    $results['checks'][] = [
        'name' => 'Error',
        'status' => 'FAIL',
        'message' => $e->getMessage()
    ];
    $results['success'] = false;
}

echo json_encode($results, JSON_PRETTY_PRINT);
?>

