<?php
/**
 * Create Admin User Script
 * Run this script to create a new admin user or reset admin password
 * Usage: php create_admin.php
 */

require_once '../config/database.php';

$conn = getDBConnection();

echo "=== Admin User Creation Tool ===\n\n";

// Default admin credentials
$defaultUsername = 'admin';
$defaultPassword = 'admin123';
$defaultEmail = 'admin@meesho.com';
$defaultFullName = 'Administrator';

echo "Creating default admin user...\n";
echo "Username: $defaultUsername\n";
echo "Password: $defaultPassword\n";
echo "Email: $defaultEmail\n\n";

// Hash password
$hashedPassword = password_hash($defaultPassword, PASSWORD_DEFAULT);

// Check if admin already exists
$checkStmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
$checkStmt->bind_param("s", $defaultUsername);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // Update existing admin
    echo "Admin user already exists. Updating password...\n";
    $updateStmt = $conn->prepare("
        UPDATE admin_users 
        SET password = ?, email = ?, full_name = ?, status = 'active'
        WHERE username = ?
    ");
    $updateStmt->bind_param("ssss", $hashedPassword, $defaultEmail, $defaultFullName, $defaultUsername);
    
    if ($updateStmt->execute()) {
        echo "✓ Admin password updated successfully!\n";
    } else {
        echo "✗ Error updating admin: " . $conn->error . "\n";
    }
    $updateStmt->close();
} else {
    // Create new admin
    echo "Creating new admin user...\n";
    $insertStmt = $conn->prepare("
        INSERT INTO admin_users (username, email, password, full_name, role, status)
        VALUES (?, ?, ?, ?, 'super_admin', 'active')
    ");
    $insertStmt->bind_param("ssss", $defaultUsername, $defaultEmail, $hashedPassword, $defaultFullName);
    
    if ($insertStmt->execute()) {
        echo "✓ Admin user created successfully!\n";
    } else {
        echo "✗ Error creating admin: " . $conn->error . "\n";
    }
    $insertStmt->close();
}

$checkStmt->close();

echo "\n=== Admin Credentials ===\n";
echo "Username: $defaultUsername\n";
echo "Password: $defaultPassword\n";
echo "Email: $defaultEmail\n";
echo "Role: Super Admin\n\n";

echo "⚠️  IMPORTANT: Change the default password after first login!\n";
echo "You can use this script to reset the password anytime.\n";

closeDBConnection($conn);
?>


