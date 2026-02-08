/**
 * Admin Panel - Main JavaScript
 */

// Toggle Sidebar
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    sidebar.classList.toggle('active');
}

// Show Message
function showMessage(message, type = 'info') {
    const messageDiv = document.getElementById('message');
    if (!messageDiv) return;
    
    messageDiv.textContent = message;
    messageDiv.className = `message ${type}`;
    messageDiv.style.display = 'block';
    
    setTimeout(() => {
        messageDiv.style.display = 'none';
    }, 5000);
}

// Show Modal
function showModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

// Hide Modal
function hideModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

// Close modal on outside click
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal')) {
        e.target.classList.remove('active');
        document.body.style.overflow = '';
    }
});

// Close modal on escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.active');
        modals.forEach(modal => {
            modal.classList.remove('active');
            document.body.style.overflow = '';
        });
    }
});

// Format Currency
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN');
}

// Format Date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

// Confirm Delete
function confirmDelete(message = 'Are you sure you want to delete this item?') {
    return confirm(message);
}

// API Request Helper
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            credentials: 'include',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            }
        });
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Error:', error);
        showMessage('An error occurred. Please try again.', 'error');
        return { success: false, message: error.message };
    }
}

// Check Authentication
async function checkAuth() {
    try {
        const response = await fetch('../api/admin/auth.php', {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (!data.success || !data.authenticated) {
            window.location.href = 'index.php';
            return false;
        }
        return true;
    } catch (error) {
        console.error('Auth check error:', error);
        return false;
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Check authentication
    checkAuth();
    
    // Auto-hide messages after 5 seconds
    const messages = document.querySelectorAll('.message');
    messages.forEach(msg => {
        if (msg.style.display !== 'none') {
            setTimeout(() => {
                msg.style.display = 'none';
            }, 5000);
        }
    });
});

