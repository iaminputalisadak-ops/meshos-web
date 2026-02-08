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

<!-- Add/Edit Brand Modal -->
<div id="brandModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="brandModalTitle">Add New Brand</h3>
            <button class="modal-close" onclick="hideModal('brandModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="brandForm" onsubmit="saveBrand(event)">
            <input type="hidden" id="brandId" name="id">
            
            <div class="form-group">
                <label for="brandName">Brand Name *</label>
                <input type="text" id="brandName" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="brandLogo">Logo URL</label>
                <input type="url" id="brandLogo" name="logo_url" placeholder="https://example.com/logo.png">
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideModal('brandModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Brand</button>
            </div>
        </form>
    </div>
</div>

<script>
let editingBrandId = null;

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
    
    let html = '<table><thead><tr><th>ID</th><th>Name</th><th>Logo</th><th>Actions</th></tr></thead><tbody>';
    
    brands.forEach(brand => {
        html += `
            <tr>
                <td>${brand.id}</td>
                <td><strong>${brand.name}</strong></td>
                <td><img src="${brand.logo_url || 'https://via.placeholder.com/50'}" alt="${brand.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" onerror="this.src='https://via.placeholder.com/50'"></td>
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
    editingBrandId = null;
    document.getElementById('brandModalTitle').textContent = 'Add New Brand';
    document.getElementById('brandForm').reset();
    document.getElementById('brandId').value = '';
    showModal('brandModal');
}

async function editBrand(id) {
    try {
        const response = await fetch(`../api/brands.php?id=${id}`);
        const data = await response.json();
        
        if (data.success && data.data) {
            const brand = data.data;
            editingBrandId = id;
            
            document.getElementById('brandModalTitle').textContent = 'Edit Brand';
            document.getElementById('brandId').value = brand.id;
            document.getElementById('brandName').value = brand.name || '';
            document.getElementById('brandLogo').value = brand.logo_url || '';
            
            showModal('brandModal');
        } else {
            showMessage('Brand not found', 'error');
        }
    } catch (error) {
        showMessage('Error loading brand', 'error');
    }
}

async function saveBrand(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const brandData = Object.fromEntries(formData);
    
    try {
        const url = '../api/brands.php';
        const method = editingBrandId ? 'PUT' : 'POST';
        
        const data = await apiRequest(url, {
            method: method,
            body: JSON.stringify(brandData)
        });
        
        if (data.success) {
            showMessage(editingBrandId ? 'Brand updated successfully' : 'Brand added successfully', 'success');
            hideModal('brandModal');
            loadBrands();
        } else {
            showMessage(data.message || 'Failed to save brand', 'error');
        }
    } catch (error) {
        showMessage('Error saving brand', 'error');
    }
}

async function deleteBrand(id) {
    if (!confirmDelete('Are you sure you want to delete this brand?')) {
        return;
    }
    
    try {
        const data = await apiRequest(`../api/brands.php?id=${id}`, {
            method: 'DELETE'
        });
        
        if (data.success) {
            showMessage('Brand deleted successfully', 'success');
            loadBrands();
        } else {
            showMessage(data.message || 'Failed to delete brand', 'error');
        }
    } catch (error) {
        showMessage('Error deleting brand', 'error');
    }
}

// Load brands on page load
loadBrands();
</script>

<?php require_once 'includes/footer.php'; ?>
