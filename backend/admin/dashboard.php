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
                <div class="value" id="totalProducts">
                    <span class="loading-spinner"></span>
                </div>
                <div class="change loading-text" id="productsChange">Loading...</div>
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
                <div class="value" id="totalCategories">
                    <span class="loading-spinner"></span>
                </div>
                <div class="change loading-text" id="categoriesChange">Loading...</div>
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
                <div class="value" id="totalOrders">
                    <span class="loading-spinner"></span>
                </div>
                <div class="change loading-text" id="ordersChange">Loading...</div>
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
                <div class="value" id="totalUsers">
                    <span class="loading-spinner"></span>
                </div>
                <div class="change loading-text" id="usersChange">Loading...</div>
            </div>
            <div class="stat-card-icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
</div>

<!-- Database Tables Overview -->
<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-database"></i> Database Tables</h2>
        <button class="btn btn-primary" onclick="refreshTables()">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
    </div>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Table Name</th>
                    <th>Rows</th>
                    <th>Size</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="tablesList">
                <tr><td colspan="4">Loading tables...</td></tr>
            </tbody>
        </table>
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
            const totalProducts = productsData.total || productsData.data?.length || 0;
            document.getElementById('totalProducts').textContent = totalProducts;
            document.getElementById('productsChange').textContent = totalProducts > 0 ? 'Active' : 'No products';
            document.getElementById('productsChange').classList.remove('loading-text');
            displayRecentProducts(productsData.data?.slice(0, 5) || []);
        } else {
            document.getElementById('totalProducts').textContent = '0';
            document.getElementById('productsChange').textContent = 'Error loading';
            document.getElementById('productsChange').classList.remove('loading-text');
        }
        
        // Load Categories
        try {
            const categoriesRes = await fetch('../api/categories.php');
            const categoriesData = await categoriesRes.json();
            if (categoriesData.success) {
                const totalCategories = categoriesData.data?.length || 0;
                document.getElementById('totalCategories').textContent = totalCategories;
                document.getElementById('categoriesChange').textContent = totalCategories > 0 ? 'Active' : 'No categories';
                document.getElementById('categoriesChange').classList.remove('loading-text');
            } else {
                document.getElementById('totalCategories').textContent = '0';
                document.getElementById('categoriesChange').textContent = 'Error loading';
                document.getElementById('categoriesChange').classList.remove('loading-text');
            }
        } catch (e) {
            document.getElementById('totalCategories').textContent = '0';
            document.getElementById('categoriesChange').textContent = 'Error loading';
            document.getElementById('categoriesChange').classList.remove('loading-text');
        }
        
        // Load Orders (if API exists)
        try {
            const ordersRes = await fetch('../api/orders.php');
            const ordersData = await ordersRes.json();
            if (ordersData.success) {
                const totalOrders = ordersData.total || ordersData.data?.length || 0;
                document.getElementById('totalOrders').textContent = totalOrders;
                document.getElementById('ordersChange').textContent = totalOrders > 0 ? 'Active' : 'No orders';
                document.getElementById('ordersChange').classList.remove('loading-text');
                displayRecentOrders(ordersData.data?.slice(0, 5) || []);
            } else {
                document.getElementById('totalOrders').textContent = '0';
                document.getElementById('ordersChange').textContent = 'No orders yet';
                document.getElementById('ordersChange').classList.remove('loading-text');
                document.getElementById('recentOrders').innerHTML = '<p class="empty-state">No orders yet</p>';
            }
        } catch (e) {
            document.getElementById('totalOrders').textContent = '0';
            document.getElementById('ordersChange').textContent = 'No orders yet';
            document.getElementById('ordersChange').classList.remove('loading-text');
            document.getElementById('recentOrders').innerHTML = '<p class="empty-state">No orders yet</p>';
        }
        
        // Load Users (if API exists)
        try {
            const usersRes = await fetch('../api/users.php');
            const usersData = await usersRes.json();
            if (usersData.success) {
                const totalUsers = usersData.total || usersData.data?.length || 0;
                document.getElementById('totalUsers').textContent = totalUsers;
                document.getElementById('usersChange').textContent = totalUsers > 0 ? 'Registered' : 'No users';
                document.getElementById('usersChange').classList.remove('loading-text');
            } else {
                document.getElementById('totalUsers').textContent = '0';
                document.getElementById('usersChange').textContent = 'No users';
                document.getElementById('usersChange').classList.remove('loading-text');
            }
        } catch (e) {
            document.getElementById('totalUsers').textContent = '0';
            document.getElementById('usersChange').textContent = 'No users';
            document.getElementById('usersChange').classList.remove('loading-text');
        }
        
        // Load Database Tables
        loadDatabaseTables();
        
    } catch (error) {
        console.error('Error loading dashboard:', error);
        showMessage('Error loading dashboard data', 'error');
    }
}

async function loadDatabaseTables() {
    try {
        const response = await fetch('get_tables.php', {
            credentials: 'include'
        });
        const data = await response.json();
        
        if (data.success) {
            displayTables(data.tables || []);
        } else {
            document.getElementById('tablesList').innerHTML = '<tr><td colspan="4">Error loading tables</td></tr>';
        }
    } catch (error) {
        console.error('Error loading tables:', error);
        document.getElementById('tablesList').innerHTML = '<tr><td colspan="4">Error loading tables</td></tr>';
    }
}

function displayTables(tables) {
    const container = document.getElementById('tablesList');
    
    if (tables.length === 0) {
        container.innerHTML = '<tr><td colspan="4">No tables found</td></tr>';
        return;
    }
    
    let html = '';
    tables.forEach(table => {
        html += `
            <tr>
                <td><strong>${table.name}</strong></td>
                <td>${table.rows || 0}</td>
                <td>${table.size || 'N/A'}</td>
                <td><span class="badge badge-success">Active</span></td>
            </tr>
        `;
    });
    
    container.innerHTML = html;
}

function refreshTables() {
    loadDatabaseTables();
    showMessage('Tables refreshed', 'success');
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
