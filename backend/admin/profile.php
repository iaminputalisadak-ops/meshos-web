<?php
/**
 * Admin Panel - Profile Settings
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once '../config/database.php';

$pageTitle = 'Profile Settings';
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDBConnection();
        
        $adminId = $_SESSION['admin_id'] ?? 1;
        $fullName = $_POST['full_name'] ?? '';
        $email = $_POST['email'] ?? '';
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Update profile info
        if (!empty($fullName) || !empty($email)) {
            $updateQuery = "UPDATE admin_users SET ";
            $updates = [];
            
            if (!empty($fullName)) {
                $updates[] = "full_name = '" . $conn->real_escape_string($fullName) . "'";
            }
            
            if (!empty($email)) {
                $updates[] = "email = '" . $conn->real_escape_string($email) . "'";
            }
            
            if (!empty($updates)) {
                $updateQuery .= implode(', ', $updates) . " WHERE id = $adminId";
                $conn->query($updateQuery);
                
                // Update session
                if (!empty($email)) {
                    $_SESSION['admin_email'] = $email;
                }
                if (!empty($fullName)) {
                    $_SESSION['admin_full_name'] = $fullName;
                }
                
                $message = 'Profile updated successfully!';
                $messageType = 'success';
            }
        }
        
        // Update password
        if (!empty($currentPassword) && !empty($newPassword)) {
            if ($newPassword !== $confirmPassword) {
                $message = 'New passwords do not match!';
                $messageType = 'error';
            } else {
                // Verify current password
                $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
                $stmt->bind_param("i", $adminId);
                $stmt->execute();
                $result = $stmt->get_result();
                $admin = $result->fetch_assoc();
                
                if ($admin && password_verify($currentPassword, $admin['password'])) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $updateStmt = $conn->prepare("UPDATE admin_users SET password = ? WHERE id = ?");
                    $updateStmt->bind_param("si", $hashedPassword, $adminId);
                    $updateStmt->execute();
                    
                    $message = 'Password updated successfully!';
                    $messageType = 'success';
                } else {
                    $message = 'Current password is incorrect!';
                    $messageType = 'error';
                }
            }
        }
        
        $conn->close();
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Get current admin data
try {
    $conn = getDBConnection();
    $adminId = $_SESSION['admin_id'] ?? 1;
    $stmt = $conn->prepare("SELECT username, email, full_name FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    $result = $stmt->get_result();
    $adminData = $result->fetch_assoc();
    $conn->close();
} catch (Exception $e) {
    $adminData = [
        'username' => $_SESSION['admin_username'] ?? 'admin',
        'email' => $_SESSION['admin_email'] ?? 'admin@meesho.com',
        'full_name' => $_SESSION['admin_full_name'] ?? 'Administrator'
    ];
}

require_once 'includes/header.php';
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-user-circle"></i> Profile Settings</h2>
    </div>
    
    <?php if ($message): ?>
        <div class="message <?php echo $messageType; ?>" style="display: block;">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <div class="profile-container">
        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-avatar">
                <i class="fas fa-user-circle"></i>
            </div>
            <h3><?php echo htmlspecialchars($adminData['full_name'] ?? $adminData['username']); ?></h3>
            <p class="profile-email"><?php echo htmlspecialchars($adminData['email']); ?></p>
            <p class="profile-username">@<?php echo htmlspecialchars($adminData['username']); ?></p>
        </div>
        
        <!-- Profile Form -->
        <div class="profile-form-container">
            <form method="POST" action="" class="profile-form">
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-user"></i> Account Information
                    </h3>
                    
                    <div class="form-group">
                        <label for="username">
                            <i class="fas fa-at"></i> Username
                        </label>
                        <input type="text" id="username" value="<?php echo htmlspecialchars($adminData['username']); ?>" disabled>
                        <small class="form-help">Username cannot be changed</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">
                            <i class="fas fa-signature"></i> Full Name
                        </label>
                        <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($adminData['full_name'] ?? ''); ?>" placeholder="Enter your full name">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-envelope"></i> Email Address
                        </label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($adminData['email'] ?? ''); ?>" placeholder="Enter your email" required>
                    </div>
                </div>
                
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-lock"></i> Change Password
                    </h3>
                    
                    <div class="form-group">
                        <label for="current_password">
                            <i class="fas fa-key"></i> Current Password
                        </label>
                        <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
                    </div>
                    
                    <div class="form-group">
                        <label for="new_password">
                            <i class="fas fa-lock"></i> New Password
                        </label>
                        <input type="password" id="new_password" name="new_password" placeholder="Enter new password" minlength="6">
                        <small class="form-help">Minimum 6 characters</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i> Confirm New Password
                        </label>
                        <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password" minlength="6">
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-large">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <button type="reset" class="btn btn-secondary btn-large">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
/* Profile Page Styles */
.profile-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 30px;
    margin-top: 20px;
}

.profile-card {
    background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    color: white;
    padding: 40px 30px;
    border-radius: 16px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(244, 51, 151, 0.3);
    position: sticky;
    top: 100px;
    height: fit-content;
}

.profile-avatar {
    font-size: 80px;
    margin-bottom: 20px;
    opacity: 0.9;
}

.profile-card h3 {
    font-size: 24px;
    margin: 10px 0;
    font-weight: 600;
}

.profile-email {
    font-size: 14px;
    opacity: 0.9;
    margin: 10px 0;
    word-break: break-word;
}

.profile-username {
    font-size: 12px;
    opacity: 0.7;
    margin-top: 5px;
}

.profile-form-container {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.profile-form {
    max-width: 600px;
}

.form-section {
    margin-bottom: 40px;
    padding-bottom: 30px;
    border-bottom: 2px solid var(--border-color);
}

.form-section:last-of-type {
    border-bottom: none;
}

.form-section-title {
    font-size: 20px;
    color: var(--dark-color);
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.form-section-title i {
    color: var(--primary-color);
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-weight: 500;
    color: var(--dark-color);
}

.form-group label i {
    color: var(--primary-color);
    width: 18px;
}

.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"] {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-group input:focus {
    outline: none;
    border-color: var(--primary-color);
    box-shadow: 0 0 0 3px rgba(244, 51, 151, 0.1);
}

.form-group input:disabled {
    background: var(--light-color);
    cursor: not-allowed;
}

.form-help {
    display: block;
    margin-top: 6px;
    font-size: 12px;
    color: #666;
}

.form-actions {
    display: flex;
    gap: 15px;
    margin-top: 30px;
    flex-wrap: wrap;
}

.btn-large {
    padding: 14px 28px;
    font-size: 16px;
    flex: 1;
    min-width: 150px;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .profile-container {
        grid-template-columns: 250px 1fr;
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .profile-container {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .profile-card {
        position: static;
        padding: 30px 20px;
    }
    
    .profile-avatar {
        font-size: 60px;
    }
    
    .profile-form-container {
        padding: 20px;
    }
    
    .form-section {
        margin-bottom: 30px;
        padding-bottom: 20px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn-large {
        width: 100%;
    }
}

@media (max-width: 480px) {
    .profile-card {
        padding: 25px 15px;
    }
    
    .profile-avatar {
        font-size: 50px;
    }
    
    .profile-card h3 {
        font-size: 20px;
    }
    
    .profile-form-container {
        padding: 15px;
    }
    
    .form-section-title {
        font-size: 18px;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>
