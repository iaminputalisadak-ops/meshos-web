<?php
/**
 * Admin Panel - Orders Management
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Orders Management';
require_once 'includes/header.php';
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-shopping-cart"></i> Orders</h2>
    </div>
    
    <div class="table-wrapper">
        <div id="ordersTable">
            <p>Loading orders...</p>
        </div>
    </div>
</div>

<script>
async function loadOrders() {
    try {
        const data = await apiRequest('../api/orders.php');
        
        if (data.success) {
            displayOrders(data.data || []);
        } else {
            document.getElementById('ordersTable').innerHTML = '<div class="empty-state"><i class="fas fa-shopping-cart"></i><h3>No Orders</h3><p>Orders will appear here when customers place orders</p></div>';
        }
    } catch (error) {
        document.getElementById('ordersTable').innerHTML = '<div class="empty-state"><i class="fas fa-shopping-cart"></i><h3>No Orders</h3><p>Orders will appear here when customers place orders</p></div>';
    }
}

function displayOrders(orders) {
    const container = document.getElementById('ordersTable');
    
    if (orders.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-shopping-cart"></i><h3>No Orders</h3><p>Orders will appear here when customers place orders</p></div>';
        return;
    }
    
    let html = '<table><thead><tr><th>Order ID</th><th>Customer</th><th>Promoter</th><th>Items</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
    
    orders.forEach(order => {
        html += `
            <tr>
                <td>#${order.id}</td>
                <td>${order.customer_name || 'Guest'}</td>
                <td>${order.promoter_code ? '<code>' + order.promoter_code + '</code>' : '-'}</td>
                <td>${order.items_count || 0}</td>
                <td>₹${order.final_amount != null ? order.final_amount : (order.total || 0)}</td>
                <td>
                    <select class="status-select" onchange="updateOrderStatus(${order.id}, this.value)">
                        <option value="pending" ${(order.status || '') === 'pending' ? 'selected' : ''}>Pending</option>
                        <option value="processing" ${(order.status || '') === 'processing' ? 'selected' : ''}>Processing</option>
                        <option value="shipped" ${(order.status || '') === 'shipped' ? 'selected' : ''}>Shipped</option>
                        <option value="delivered" ${(order.status || '') === 'delivered' ? 'selected' : ''}>Delivered</option>
                        <option value="cancelled" ${(order.status || '') === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                    </select>
                </td>
                <td>${order.created_at ? formatDate(order.created_at) : 'N/A'}</td>
                <td>
                    <button class="btn btn-primary btn-sm" onclick="viewOrder(${order.id})">
                        <i class="fas fa-eye"></i> View
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function viewOrder(id) {
    alert('Order details for order #' + id + '\n\nThis feature will show detailed order information.');
}

async function updateOrderStatus(orderId, status) {
    try {
        const res = await fetch('../api/admin/order_status.php', {
            method: 'POST',
            credentials: 'include',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ order_id: orderId, status })
        });
        const data = await res.json();
        if (data.success) {
            loadOrders();
        } else {
            alert(data.message || 'Update failed');
        }
    } catch (e) {
        alert('Request failed');
    }
}

loadOrders();
</script>

<?php require_once 'includes/footer.php'; ?>

