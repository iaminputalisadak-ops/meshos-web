<?php
/**
 * CORS Configuration for React Frontend and Admin Panel
 */

// Get the origin of the request
$allowed_origins = [
    'http://localhost:3000',
    'http://localhost',
    'http://127.0.0.1',
    'http://127.0.0.1:3000',
    'http://localhost:3001', // Alternative React port
    'http://127.0.0.1:3001'
];

$origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
$request_origin = isset($_SERVER['HTTP_HOST']) ? 'http://' . $_SERVER['HTTP_HOST'] : '';

// Allow requests from same origin (admin panel) or from allowed origins
if (in_array($origin, $allowed_origins) || empty($origin) || strpos($request_origin, 'localhost') !== false) {
    if (!empty($origin)) {
        header('Access-Control-Allow-Origin: ' . $origin);
    } else {
        // For same-origin requests (admin panel), allow all for development
        header('Access-Control-Allow-Origin: *');
    }
} else {
    // Default: allow all origins for development
    header('Access-Control-Allow-Origin: *');
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=UTF-8');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
?>


