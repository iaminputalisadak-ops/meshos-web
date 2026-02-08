<?php
/**
 * Admin Authentication Check
 * Use this to verify if admin is logged in
 */

session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized - Please login',
        'authenticated' => false
    ]);
    exit;
}

// Admin is authenticated
echo json_encode([
    'success' => true,
    'authenticated' => true,
    'data' => [
        'admin_id' => $_SESSION['admin_id'],
        'username' => $_SESSION['admin_username'],
        'role' => $_SESSION['admin_role']
    ]
]);
?>


