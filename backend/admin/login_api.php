<?php
/**
 * Admin Login API - Proxy file in admin folder
 * This avoids path resolution issues
 */

// CRITICAL: Turn off all output and start buffering
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ob_start();

// Set headers first
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    ob_clean();
    http_response_code(200);
    ob_end_flush();
    exit;
}

// Include the actual login API
$apiPath = __DIR__ . '/../api/admin/login.php';
if (file_exists($apiPath)) {
    try {
        require_once $apiPath;
    } catch (Exception $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Login API error: ' . $e->getMessage(),
            'error' => $e->getMessage(),
            'setup_needed' => true,
            'setup_url' => '../database/setup_database.php'
        ]);
        ob_end_flush();
    } catch (Error $e) {
        ob_clean();
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Login API fatal error: ' . $e->getMessage(),
            'error' => $e->getMessage(),
            'setup_needed' => true,
            'setup_url' => '../database/setup_database.php'
        ]);
        ob_end_flush();
    }
} else {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Login API not found. Path: ' . $apiPath,
        'setup_needed' => true,
        'setup_url' => '../database/setup_database.php'
    ]);
    ob_end_flush();
}

