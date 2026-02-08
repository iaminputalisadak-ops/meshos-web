<?php
/**
 * Admin Panel - Settings
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Settings';
require_once 'includes/header.php';
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-cog"></i> Settings</h2>
    </div>
    
    <div class="form-group">
        <h3>Admin Profile</h3>
        <p>Username: <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong></p>
        <p>Email: <strong><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'N/A'); ?></strong></p>
    </div>
    
    <div class="form-group">
        <h3>System Information</h3>
        <p>PHP Version: <strong><?php echo phpversion(); ?></strong></p>
        <p>Server: <strong><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></strong></p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

