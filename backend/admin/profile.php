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
        
        $adminId = $_SESSION['admin_id'];
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
                
                if (password_verify($currentPassword, $admin['password'])) {
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
    $adminId = $_SESSION['admin_id'];
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
        <div class="message <?php echo $messageType; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <h3>Account Information</h3>
        </div>
        
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" value="<?php echo htmlspecialchars($adminData['username']); ?>" disabled>
            <small style="color: #999;">Username cannot be changed</small>
        </div>
        
        <div class="form-group">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($adminData['full_name'] ?? ''); ?>" placeholder="Enter your full name">
        </div>
        
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($adminData['email'] ?? ''); ?>" placeholder="Enter your email" required>
        </div>
        
        <div class="form-group" style="margin-top: 30px;">
            <h3>Change Password</h3>
        </div>
        
        <div class="form-group">
            <label for="current_password">Current Password</label>
            <input type="password" id="current_password" name="current_password" placeholder="Enter current password">
        </div>
        
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" id="new_password" name="new_password" placeholder="Enter new password">
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Confirm New Password</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>

