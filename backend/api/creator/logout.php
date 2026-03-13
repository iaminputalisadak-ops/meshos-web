<?php
/**
 * Creator Partner Logout
 */
require_once __DIR__ . '/../../config/cors.php';
header('Content-Type: application/json; charset=utf-8');

session_set_cookie_params(['path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
session_start();

unset($_SESSION['creator_id'], $_SESSION['creator_code'], $_SESSION['creator_logged_in']);
session_destroy();

echo json_encode(['success' => true, 'message' => 'Logged out']);
