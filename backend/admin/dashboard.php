<?php
/**
 * Admin Panel - Dashboard (Main Page)
 */
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Dashboard';
require_once 'includes/header.php';
?>

<!-- Dashboard Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <h3>Total Products</h3>
                <div class="value" id="totalProducts">-</div>
                <div class="change" id="productsChange">Loading...</div>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-box"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <h3>Categories</h3>
                <div class="value" id="totalCategories">-</div>
                <div class="change" id="categoriesChange">Loading...</div>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-tags"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <h3>Total Orders</h3>
                <div class="value" id="totalOrders">-</div>
                <div class="change" id="ordersChange">Loading...</div>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-shopping-cart"></i>
            </div>
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <div>
                <h3>Total Users</h3>
                <div class="value" id="totalUsers">-</div>
                <div class="change" id="usersChange">Loading...</div>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Products -->
<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-box"></i> Recent Products</h2>
        <a href="products.php" class="btn btn-primary">
            <i class="fas fa-eye"></i> View All
        </a>
    </div>
    <div class="table-wrapper">
        <div id="recentProducts">
            <p>Loading products...</p>
        </div>
    </div>
</div>

<!-- Recent Orders -->
<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-shopping-cart"></i> Recent Orders</h2>
        <a href="orders.php" class="btn btn-primary">
            <i class="fas fa-eye"></i> View All
        </a>
    </div>
    <div class="table-wrapper">
        <div id="recentOrders">
            <p>Loading orders...</p>
        </div>
    </div>
</div>

<script>
// Load Dashboard Data
async function loadDashboard() {
    try {
        // Load Products
        const productsRes = await fetch('../api/admin/products.php', {
            credentials: 'include'
        });
        const productsData = await productsRes.json();
        
        if (productsData.success) {
            document.getElementById('totalProducts').textContent = productsData.total || 0;
            displayRecentProducts(productsData.data?.slice(0, 5) || []);
        }
        
        // Load Categories
        const categoriesRes = await fetch('../api/categories.php');
        const categoriesData = await categoriesRes.json();
        if (categoriesData.success) {
            document.getElementById('totalCategories').textContent = categoriesData.data?.length || 0;
        }
        
        // Load Orders (if API exists)
        try {
            const ordersRes = await fetch('../api/orders.php');
            const ordersData = await ordersRes.json();
            if (ordersData.success) {
                document.getElementById('totalOrders').textContent = ordersData.total || 0;
                displayRecentOrders(ordersData.data?.slice(0, 5) || []);
            }
        } catch (e) {
            document.getElementById('totalOrders').textContent = '0';
            document.getElementById('recentOrders').innerHTML = '<p class="empty-state">No orders yet</p>';
        }
        
        // Load Users (if API exists)
        try {
            const usersRes = await fetch('../api/users.php');
            const usersData = await usersRes.json();
            if (usersData.success) {
                document.getElementById('totalUsers').textContent = usersData.total || 0;
            }
        } catch (e) {
            document.getElementById('totalUsers').textContent = '0';
        }
        
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showMessage('Error loading dashboard data', 'error');
    }
}

function displayRecentProducts(products) {
    const container = document.getElementById('recentProducts');
    
    if (products.length === 0) {
        container.innerHTML = '<p class="empty-state">No products found</p>';
        return;
    }
    
    let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead><tbody>';
    
    products.forEach(product => {
        html += `
            <tr>
                <td>${product.id}</td>
                <td>${product.name}</td>
                <td>${product.category_name || 'N/A'}</td>
                <td>₹${product.price}</td>
                <td><span class="badge ${product.in_stock ? 'badge-success' : 'badge-danger'}">${product.in_stock ? 'In Stock' : 'Out of Stock'}</span></td>
                <td>
                    <a href="products.php?edit=${product.id}" class="btn btn-success btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function displayRecentOrders(orders) {
    const container = document.getElementById('recentOrders');
    
    if (orders.length === 0) {
        container.innerHTML = '<p class="empty-state">No orders found</p>';
        return;
    }
    
    let html = '<table><thead><tr><th>Order ID</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th><th>Actions</th></tr></thead><tbody>';
    
    orders.forEach(order => {
        html += `
            <tr>
                <td>#${order.id}</td>
                <td>${order.customer_name || 'N/A'}</td>
                <td>₹${order.total}</td>
                <td><span class="badge badge-info">${order.status || 'Pending'}</span></td>
                <td>${order.created_at ? new Date(order.created_at).toLocaleDateString() : 'N/A'}</td>
                <td>
                    <a href="orders.php?view=${order.id}" class="btn btn-primary btn-sm">
                        <i class="fas fa-eye"></i> View
                    </a>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

// Load dashboard on page load
loadDashboard();
</script>

<?php require_once 'includes/footer.php'; ?>
