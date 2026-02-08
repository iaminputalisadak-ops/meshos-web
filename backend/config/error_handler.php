<?php
/**
 * Error Handler Configuration
 */

// Set error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 in production
ini_set('log_errors', 1);

/**
 * Custom error handler
 */
function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    // Don't handle errors if headers already sent
    if (headers_sent()) {
        return false;
    }
    
    // Only handle if it's an API request (JSON response expected)
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred: ' . $errstr,
            'error' => $errstr,
            'file' => basename($errfile),
            'line' => $errline
        ]);
        exit;
    }
    
    return false; // Let PHP handle it normally for non-API requests
}

set_error_handler('handleError');

/**
 * Custom exception handler
 */
function handleException($exception) {
    // Don't handle if headers already sent
    if (headers_sent()) {
        return;
    }
    
    // Only handle if it's an API request (JSON response expected)
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'An exception occurred: ' . $exception->getMessage(),
            'error' => $exception->getMessage(),
            'file' => basename($exception->getFile()),
            'line' => $exception->getLine()
        ]);
        exit;
    }
    
    // For non-API requests, show user-friendly error
    http_response_code(500);
    echo "An error occurred. Please check the server logs.";
    exit;
}

set_exception_handler('handleException');
?>


