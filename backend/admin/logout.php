<?php
/**
 * Admin Panel - Logout Handler
 */
session_start();

// Get username for logging (before clearing session)
$username = $_SESSION['admin_username'] ?? 'Unknown';

// Clear all session data
$_SESSION = array();

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Destroy the session
session_destroy();

// Set logout message in a way that can be displayed on login page
// Using GET parameter for simplicity
header('Location: index.php?logout=success&user=' . urlencode($username));
exit;
?>

