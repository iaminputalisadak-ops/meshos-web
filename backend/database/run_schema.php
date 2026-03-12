<?php
/**
 * Run schema.sql to create database and all tables.
 * Uses config from backend/config/database.php (same host/user/pass; DB name is in schema).
 *
 * From project root:
 *   php backend/database/run_schema.php
 * Or in browser (XAMPP):
 *   http://localhost/backend/database/run_schema.php
 */

$isCli = (php_sapi_name() === 'cli');

if (!$isCli) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<!DOCTYPE html><html><head><title>Run schema</title>";
    echo "<style>body{font-family:sans-serif;max-width:700px;margin:40px auto;padding:20px;}";
    echo ".ok{color:#0a0;} .err{color:#c00;} pre{background:#f4f4f4;padding:10px;}</style></head><body><pre>";
}

require_once __DIR__ . '/../config/database.php';

$schemaPath = __DIR__ . '/schema.sql';
if (!is_readable($schemaPath)) {
    $msg = "schema.sql not found at: $schemaPath";
    if ($isCli) { fwrite(STDERR, $msg . "\n"); exit(1); }
    echo "<span class='err'>$msg</span>"; echo "</pre></body></html>"; exit(1);
}

$sql = file_get_contents($schemaPath);
// Remove single-line comments and empty lines for cleaner execution
$sql = preg_replace('/--[^\n]*\n/', "\n", $sql);
$sql = trim($sql);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
if ($conn->connect_error) {
    $msg = "MySQL connection failed: " . $conn->connect_error . "\nMake sure MySQL is running (e.g. XAMPP).";
    if ($isCli) { fwrite(STDERR, $msg . "\n"); exit(1); }
    echo "<span class='err'>" . htmlspecialchars($msg) . "</span></pre></body></html>"; exit(1);
}

$conn->set_charset("utf8mb4");

if ($conn->multi_query($sql)) {
    do {
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
}

if ($conn->errno) {
    $msg = "SQL Error: " . $conn->error;
    if ($isCli) { fwrite(STDERR, $msg . "\n"); $conn->close(); exit(1); }
    echo "<span class='err'>" . htmlspecialchars($msg) . "</span></pre></body></html>"; $conn->close(); exit(1);
}

$conn->close();

$msg = "Database 'meesho_ecommerce' and all tables created successfully. Categories and default admin (admin / admin123) inserted.";
if ($isCli) {
    echo $msg . "\n";
    exit(0);
}
echo "<span class='ok'>" . htmlspecialchars($msg) . "</span>";
echo "</pre><p><a href='../admin/'>Admin panel</a> | <a href='setup_database.php'>Full setup page</a></p></body></html>";
