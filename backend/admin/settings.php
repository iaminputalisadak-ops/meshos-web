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

// Get database info
$dbName = 'N/A';
$dbStatus = 'Unknown';
try {
    require_once '../config/database.php';
    $dbName = DB_NAME;
    $conn = getDBConnection();
    if ($conn) {
        $dbStatus = 'Connected';
        $conn->close();
    }
} catch (Exception $e) {
    $dbStatus = 'Error: ' . $e->getMessage();
}
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-cog"></i> System Settings</h2>
    </div>
    
    <div class="settings-grid">
        <!-- Admin Profile Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fas fa-user-circle"></i>
                <h3>Admin Profile</h3>
            </div>
            <div class="settings-card-body">
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-at"></i> Username
                    </span>
                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-envelope"></i> Email
                    </span>
                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_email'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-signature"></i> Full Name
                    </span>
                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_full_name'] ?? 'Administrator'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-user-tag"></i> Role
                    </span>
                    <span class="info-value badge badge-primary"><?php echo htmlspecialchars($_SESSION['admin_role'] ?? 'admin'); ?></span>
                </div>
                <div class="settings-card-footer">
                    <a href="profile.php" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                </div>
            </div>
        </div>
        
        <!-- System Information Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fas fa-server"></i>
                <h3>System Information</h3>
            </div>
            <div class="settings-card-body">
                <div class="info-item">
                    <span class="info-label">
                        <i class="fab fa-php"></i> PHP Version
                    </span>
                    <span class="info-value"><?php echo phpversion(); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-server"></i> Server
                    </span>
                    <span class="info-value"><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'N/A'; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-database"></i> Database
                    </span>
                    <span class="info-value"><?php echo $dbName; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">
                        <i class="fas fa-clock"></i> Server Time
                    </span>
                    <span class="info-value" id="serverTime"><?php echo date('Y-m-d H:i:s'); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Database Status Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fas fa-database"></i>
                <h3>Database Status</h3>
            </div>
            <div class="settings-card-body">
                <div class="status-indicator" id="dbStatusIndicator">
                    <div class="status-dot"></div>
                    <span id="dbStatusText">Checking...</span>
                </div>
                <div class="status-details" id="dbStatusDetails">
                    <p>Verifying database connection...</p>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions Card -->
        <div class="settings-card">
            <div class="settings-card-header">
                <i class="fas fa-bolt"></i>
                <h3>Quick Actions</h3>
            </div>
            <div class="settings-card-body">
                <div class="quick-actions">
                    <a href="dashboard.php" class="action-btn">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="products.php" class="action-btn">
                        <i class="fas fa-box"></i>
                        <span>Products</span>
                    </a>
                    <a href="categories.php" class="action-btn">
                        <i class="fas fa-tags"></i>
                        <span>Categories</span>
                    </a>
                    <a href="orders.php" class="action-btn">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Orders</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Update server time
function updateServerTime() {
    const now = new Date();
    const timeString = now.toLocaleString('en-US', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
    const timeEl = document.getElementById('serverTime');
    if (timeEl) {
        timeEl.textContent = timeString;
    }
}

setInterval(updateServerTime, 1000);

// Check database status
async function checkDatabaseStatus() {
    try {
        const response = await fetch('check_setup.php');
        const data = await response.json();
        
        const indicator = document.getElementById('dbStatusIndicator');
        const text = document.getElementById('dbStatusText');
        const details = document.getElementById('dbStatusDetails');
        
        if (data.success) {
            indicator.className = 'status-indicator status-success';
            text.textContent = 'Connected';
            details.innerHTML = '<p style="color: var(--success-color);"><i class="fas fa-check-circle"></i> Database is connected and working properly</p>';
        } else {
            indicator.className = 'status-indicator status-error';
            text.textContent = 'Error';
            details.innerHTML = '<p style="color: var(--danger-color);"><i class="fas fa-exclamation-circle"></i> ' + (data.message || 'Database connection issue') + '</p>';
        }
    } catch (error) {
        const indicator = document.getElementById('dbStatusIndicator');
        const text = document.getElementById('dbStatusText');
        const details = document.getElementById('dbStatusDetails');
        
        indicator.className = 'status-indicator status-warning';
        text.textContent = 'Unknown';
        details.innerHTML = '<p style="color: var(--warning-color);"><i class="fas fa-exclamation-triangle"></i> Unable to check database status</p>';
    }
}

checkDatabaseStatus();
</script>

<style>
/* Settings Page Styles */
.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-top: 20px;
}

.settings-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    overflow: hidden;
    transition: transform 0.3s, box-shadow 0.3s;
}

.settings-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.12);
}

.settings-card-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.settings-card-header i {
    font-size: 24px;
}

.settings-card-header h3 {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
}

.settings-card-body {
    padding: 25px;
}

.settings-card-footer {
    padding: 15px 25px;
    border-top: 1px solid var(--border-color);
    background: var(--light-color);
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #666;
    font-size: 14px;
}

.info-label i {
    color: var(--primary-color);
    width: 18px;
}

.info-value {
    font-weight: 600;
    color: var(--dark-color);
    text-align: right;
    word-break: break-word;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    background: var(--light-color);
}

.status-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #999;
    animation: pulse 2s infinite;
}

.status-success .status-dot {
    background: var(--success-color);
}

.status-error .status-dot {
    background: var(--danger-color);
}

.status-warning .status-dot {
    background: var(--warning-color);
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.status-details {
    font-size: 14px;
    line-height: 1.6;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
}

.action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 20px;
    background: var(--light-color);
    border-radius: 8px;
    text-decoration: none;
    color: var(--dark-color);
    transition: all 0.3s;
    border: 2px solid transparent;
}

.action-btn:hover {
    background: white;
    border-color: var(--primary-color);
    color: var(--primary-color);
    transform: translateY(-2px);
}

.action-btn i {
    font-size: 24px;
    color: var(--primary-color);
}

.action-btn span {
    font-size: 13px;
    font-weight: 500;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .settings-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .settings-grid {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .settings-card-header {
        padding: 15px;
    }
    
    .settings-card-body {
        padding: 20px;
    }
    
    .info-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 5px;
    }
    
    .info-value {
        text-align: left;
    }
    
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .settings-card-header {
        padding: 12px;
    }
    
    .settings-card-header h3 {
        font-size: 16px;
    }
    
    .settings-card-body {
        padding: 15px;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .action-btn {
        flex-direction: row;
        justify-content: flex-start;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
