<?php
/**
 * Error Handler Configuration
 */

// Set error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0); // Set to 0 in production

/**
 * Custom error handler
 */
function handleError($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred',
        'error' => $errstr
    ]);
    exit;
}

set_error_handler('handleError');

/**
 * Custom exception handler
 */
function handleException($exception) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An exception occurred',
        'error' => $exception->getMessage()
    ]);
    exit;
}

set_exception_handler('handleException');
?>


