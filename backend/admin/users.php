<?php
/**
 * Admin Panel - Users Management
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Users Management';
require_once 'includes/header.php';
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-users"></i> Users</h2>
    </div>
    
    <div class="table-wrapper">
        <div id="usersTable">
            <p>Loading users...</p>
        </div>
    </div>
</div>

<script>
async function loadUsers() {
    try {
        const data = await apiRequest('../api/users.php');
        
        if (data.success) {
            displayUsers(data.data || []);
        } else {
            document.getElementById('usersTable').innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><h3>No Users</h3><p>Users will appear here when they register</p></div>';
        }
    } catch (error) {
        document.getElementById('usersTable').innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><h3>No Users</h3><p>Users will appear here when they register</p></div>';
    }
}

function displayUsers(users) {
    const container = document.getElementById('usersTable');
    
    if (users.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-users"></i><h3>No Users</h3><p>Users will appear here when they register</p></div>';
        return;
    }
    
    let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Registered</th><th>Status</th><th>Actions</th></tr></thead><tbody>';
    
    users.forEach(user => {
        html += `
            <tr>
                <td>${user.id}</td>
                <td>${user.name || 'N/A'}</td>
                <td>${user.email || 'N/A'}</td>
                <td>${user.phone || 'N/A'}</td>
                <td>${user.created_at ? formatDate(user.created_at) : 'N/A'}</td>
                <td><span class="badge badge-success">Active</span></td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="viewUser(${user.id})">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function viewUser(id) {
    alert('User details for user #' + id + '\n\nThis feature will show detailed user information.');
}

loadUsers();
</script>

<?php require_once 'includes/footer.php'; ?>

