<?php
/**
 * Admin Panel - Products Management
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Products Management';
require_once 'includes/header.php';
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-box"></i> Products</h2>
        <button class="btn btn-primary" onclick="showAddProductModal()">
            <i class="fas fa-plus"></i> Add New Product
        </button>
    </div>
    
    <div class="table-wrapper">
        <div id="productsTable">
            <p>Loading products...</p>
        </div>
    </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="modalTitle">Add New Product</h3>
            <button class="modal-close" onclick="hideModal('productModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="productForm" onsubmit="saveProduct(event)">
            <input type="hidden" id="productId" name="id">
            
            <div class="form-group">
                <label for="productName">Product Name *</label>
                <input type="text" id="productName" name="name" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="productCategory">Category *</label>
                    <select id="productCategory" name="category_id" required>
                        <option value="">Select Category</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="productPrice">Price (₹) *</label>
                    <input type="number" id="productPrice" name="price" step="0.01" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="productOriginalPrice">Original Price (₹)</label>
                    <input type="number" id="productOriginalPrice" name="original_price" step="0.01">
                </div>
                <div class="form-group">
                    <label for="productDiscount">Discount (%)</label>
                    <input type="number" id="productDiscount" name="discount" step="0.01">
                </div>
            </div>
            
            <div class="form-group">
                <label for="productImage">Image URL</label>
                <input type="url" id="productImage" name="image">
            </div>
            
            <div class="form-group">
                <label for="productDescription">Description</label>
                <textarea id="productDescription" name="description" rows="4"></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="productRating">Rating</label>
                    <input type="number" id="productRating" name="rating" step="0.1" min="0" max="5">
                </div>
                <div class="form-group">
                    <label for="productReviews">Reviews Count</label>
                    <input type="number" id="productReviews" name="reviews" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" id="productInStock" name="in_stock" checked>
                    In Stock
                </label>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideModal('productModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Product</button>
            </div>
        </form>
    </div>
</div>

<script>
let categories = [];
let editingProductId = null;

// Load Products
async function loadProducts() {
    try {
        const container = document.getElementById('productsTable');
        container.innerHTML = '<p class="loading-text">Loading products...</p>';
        
        const data = await apiRequest('../api/admin/products.php');
        
        if (data.success) {
            displayProducts(data.data || []);
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><h3>No Products</h3><p>Add your first product to get started</p></div>';
            showMessage(data.message || 'Failed to load products', 'error');
        }
    } catch (error) {
        document.getElementById('productsTable').innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><h3>No Products</h3><p>Add your first product to get started</p></div>';
        showMessage('Error loading products', 'error');
    }
}

// Load Categories
async function loadCategories() {
    try {
        const data = await apiRequest('../api/categories.php');
        
        if (data.success) {
            categories = data.data || [];
            const select = document.getElementById('productCategory');
            select.innerHTML = '<option value="">Select Category</option>';
            categories.forEach(cat => {
                select.innerHTML += `<option value="${cat.id}">${cat.name}</option>`;
            });
        }
    } catch (error) {
        console.error('Error loading categories:', error);
    }
}

function displayProducts(products) {
    const container = document.getElementById('productsTable');
    
    if (products.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-box-open"></i><h3>No Products</h3><p>Add your first product to get started</p></div>';
        return;
    }
    
    let html = '<table><thead><tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr></thead><tbody>';
    
    products.forEach(product => {
        const inStock = product.in_stock !== undefined ? Boolean(product.in_stock) : true;
        html += `
            <tr>
                <td>${product.id}</td>
                <td><img src="${product.image || 'https://via.placeholder.com/50'}" alt="${product.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" onerror="this.src='https://via.placeholder.com/50'"></td>
                <td><strong>${product.name}</strong></td>
                <td>${product.category_name || 'N/A'}</td>
                <td>₹${parseFloat(product.price || 0).toFixed(2)}</td>
                <td><span class="badge ${inStock ? 'badge-success' : 'badge-danger'}">${inStock ? 'In Stock' : 'Out of Stock'}</span></td>
                <td>
                    <button class="btn btn-success btn-sm" onclick="editProduct(${product.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteProduct(${product.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function showAddProductModal() {
    editingProductId = null;
    document.getElementById('modalTitle').textContent = 'Add New Product';
    document.getElementById('productForm').reset();
    document.getElementById('productId').value = '';
    showModal('productModal');
}

async function editProduct(id) {
    try {
        const response = await fetch(`../api/products.php?id=${id}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            const product = data.data;
            editingProductId = id;
            
            document.getElementById('modalTitle').textContent = 'Edit Product';
            document.getElementById('productId').value = product.id;
            document.getElementById('productName').value = product.name || '';
            document.getElementById('productCategory').value = product.category_id || '';
            document.getElementById('productPrice').value = product.price || '';
            document.getElementById('productOriginalPrice').value = product.original_price || product.price || '';
            document.getElementById('productDiscount').value = product.discount || 0;
            document.getElementById('productImage').value = product.image || '';
            document.getElementById('productDescription').value = product.description || '';
            document.getElementById('productRating').value = product.rating || 0;
            document.getElementById('productReviews').value = product.reviews || 0;
            document.getElementById('productInStock').checked = product.in_stock !== undefined ? Boolean(product.in_stock) : true;
            
            showModal('productModal');
        } else {
            showMessage('Product not found', 'error');
        }
    } catch (error) {
        showMessage('Error loading product', 'error');
    }
}

async function saveProduct(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const productData = Object.fromEntries(formData);
    
    // Convert checkbox value
    productData.in_stock = document.getElementById('productInStock').checked ? 1 : 0;
    
    // Ensure original_price is set
    if (!productData.original_price || productData.original_price === '') {
        productData.original_price = productData.price;
    }
    
    // Calculate discount if not provided
    if (!productData.discount || productData.discount === '') {
        const price = parseFloat(productData.price);
        const originalPrice = parseFloat(productData.original_price);
        if (originalPrice > price) {
            productData.discount = Math.round(((originalPrice - price) / originalPrice) * 100);
        } else {
            productData.discount = 0;
        }
    }
    
    try {
        const url = '../api/admin/products.php';
        const method = editingProductId ? 'PUT' : 'POST';
        
        const data = await apiRequest(url, {
            method: method,
            body: JSON.stringify(productData)
        });
        
        if (data.success) {
            showMessage(editingProductId ? 'Product updated successfully' : 'Product added successfully', 'success');
            hideModal('productModal');
            loadProducts();
        } else {
            showMessage(data.message || 'Failed to save product', 'error');
        }
    } catch (error) {
        showMessage('Error saving product: ' + error.message, 'error');
    }
}

async function deleteProduct(id) {
    if (!confirmDelete('Are you sure you want to delete this product?')) {
        return;
    }
    
    try {
        const data = await apiRequest(`../api/admin/products.php?id=${id}`, {
            method: 'DELETE'
        });
        
        if (data.success) {
            showMessage('Product deleted successfully', 'success');
            loadProducts();
        } else {
            showMessage(data.message || 'Failed to delete product', 'error');
        }
    } catch (error) {
        showMessage('Error deleting product: ' + error.message, 'error');
    }
}

// Load data on page load
loadCategories();
loadProducts();
</script>

<?php require_once 'includes/footer.php'; ?>

