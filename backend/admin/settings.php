<?php
/**
 * Admin Panel - System Settings
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
        <h2><i class="fas fa-cog"></i> System Settings</h2>
    </div>
    
    <div class="form-group">
        <h3><i class="fas fa-user-circle"></i> Admin Profile</h3>
        <div style="background: var(--light-color); padding: 15px; border-radius: 8px; margin-top: 10px;">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['admin_username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'N/A'); ?></p>
            <p><strong>Full Name:</strong> <?php echo htmlspecialchars($_SESSION['admin_full_name'] ?? 'Administrator'); ?></p>
            <p><strong>Role:</strong> <?php echo htmlspecialchars($_SESSION['admin_role'] ?? 'admin'); ?></p>
            <div style="margin-top: 15px;">
                <a href="profile.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>
    
    <div class="form-group">
        <h3><i class="fas fa-server"></i> System Information</h3>
        <div style="background: var(--light-color); padding: 15px; border-radius: 8px; margin-top: 10px;">
            <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
            <p><strong>Server:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></p>
            <p><strong>Database:</strong> <?php 
                try {
                    require_once '../config/database.php';
                    echo DB_NAME;
                } catch (Exception $e) {
                    echo 'N/A';
                }
            ?></p>
            <p><strong>Server Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>
    </div>
    
    <div class="form-group">
        <h3><i class="fas fa-database"></i> Database Status</h3>
        <div style="background: var(--light-color); padding: 15px; border-radius: 8px; margin-top: 10px;">
            <p id="dbStatus">Checking database status...</p>
        </div>
    </div>
</div>

<script>
// Check database status
async function checkDatabaseStatus() {
    try {
        const response = await fetch('check_setup.php');
        const data = await response.json();
        
        const statusDiv = document.getElementById('dbStatus');
        if (data.success) {
            statusDiv.innerHTML = '<span style="color: var(--success-color);"><i class="fas fa-check-circle"></i> Database is connected and working properly</span>';
        } else {
            statusDiv.innerHTML = '<span style="color: var(--danger-color);"><i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Database connection issue') + '</span>';
        }
    } catch (error) {
        document.getElementById('dbStatus').innerHTML = '<span style="color: var(--warning-color);"><i class="fas fa-exclamation-triangle"></i> Unable to check database status</span>';
    }
}

checkDatabaseStatus();
</script>

<?php require_once 'includes/footer.php'; ?>

