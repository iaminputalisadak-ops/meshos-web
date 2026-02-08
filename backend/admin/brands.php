<?php
/**
 * Admin Panel - Brands Management
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Brands Management';
require_once 'includes/header.php';
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-star"></i> Brands</h2>
        <button class="btn btn-primary" onclick="showAddBrandModal()">
            <i class="fas fa-plus"></i> Add New Brand
        </button>
    </div>
    
    <div class="table-wrapper">
        <div id="brandsTable">
            <p>Loading brands...</p>
        </div>
    </div>
</div>

<script>
async function loadBrands() {
    try {
        const data = await apiRequest('../api/brands.php');
        
        if (data.success) {
            displayBrands(data.data || []);
        } else {
            document.getElementById('brandsTable').innerHTML = '<div class="empty-state"><i class="fas fa-star"></i><h3>No Brands</h3><p>Add your first brand to get started</p></div>';
        }
    } catch (error) {
        document.getElementById('brandsTable').innerHTML = '<div class="empty-state"><i class="fas fa-star"></i><h3>No Brands</h3><p>Add your first brand to get started</p></div>';
    }
}

function displayBrands(brands) {
    const container = document.getElementById('brandsTable');
    
    if (brands.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-star"></i><h3>No Brands</h3><p>Add your first brand to get started</p></div>';
        return;
    }
    
    let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Logo</th><th>Actions</th></tr></thead><tbody>';
    
    brands.forEach(brand => {
        html += `
            <tr>
                <td>${brand.id}</td>
                <td>${brand.name}</td>
                <td>${brand.category || 'N/A'}</td>
                <td><img src="${brand.logo || brand.product_image || 'https://via.placeholder.com/50'}" alt="${brand.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" onerror="this.src='https://via.placeholder.com/50'"></td>
                <td>
                    <button class="btn btn-success btn-sm" onclick="editBrand(${brand.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteBrand(${brand.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function showAddBrandModal() {
    alert('Add Brand feature - Coming soon!');
}

function editBrand(id) {
    alert('Edit Brand #' + id + ' - Coming soon!');
}

function deleteBrand(id) {
    if (confirmDelete('Are you sure you want to delete this brand?')) {
        alert('Delete Brand #' + id + ' - Coming soon!');
    }
}

loadBrands();
</script>

<?php require_once 'includes/footer.php'; ?>

