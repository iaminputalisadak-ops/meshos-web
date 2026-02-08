<?php
/**
 * Admin Panel - Categories Management
 */
session_start();

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Categories Management';
require_once 'includes/header.php';
?>

<div class="section">
    <div class="section-header">
        <h2><i class="fas fa-tags"></i> Categories</h2>
        <button class="btn btn-primary" onclick="showAddCategoryModal()">
            <i class="fas fa-plus"></i> Add New Category
        </button>
    </div>
    
    <div class="table-wrapper">
        <div id="categoriesTable">
            <p>Loading categories...</p>
        </div>
    </div>
</div>

<!-- Add/Edit Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 id="categoryModalTitle">Add New Category</h3>
            <button class="modal-close" onclick="hideModal('categoryModal')">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="categoryForm" onsubmit="saveCategory(event)">
            <input type="hidden" id="categoryId" name="id">
            
            <div class="form-group">
                <label for="categoryName">Category Name *</label>
                <input type="text" id="categoryName" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="categoryIcon">Icon (Emoji or Font Awesome class)</label>
                <input type="text" id="categoryIcon" name="icon" placeholder="e.g., 👗 or fas fa-tshirt">
            </div>
            
            <div class="form-group">
                <label for="categoryImage">Image URL</label>
                <input type="url" id="categoryImage" name="image">
            </div>
            
            <div class="form-group">
                <label for="categoryDescription">Description</label>
                <textarea id="categoryDescription" name="description" rows="3"></textarea>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="hideModal('categoryModal')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </form>
    </div>
</div>

<script>
let editingCategoryId = null;

async function loadCategories() {
    try {
        const data = await apiRequest('../api/categories.php');
        
        if (data.success) {
            displayCategories(data.data || []);
        } else {
            showMessage(data.message || 'Failed to load categories', 'error');
        }
    } catch (error) {
        showMessage('Error loading categories', 'error');
    }
}

function displayCategories(categories) {
    const container = document.getElementById('categoriesTable');
    
    if (categories.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-tags"></i><h3>No Categories</h3><p>Add your first category to get started</p></div>';
        return;
    }
    
    let html = '<table><thead><tr><th>ID</th><th>Icon</th><th>Name</th><th>Image</th><th>Products</th><th>Actions</th></tr></thead><tbody>';
    
    categories.forEach(category => {
        html += `
            <tr>
                <td>${category.id}</td>
                <td>${category.icon || '—'}</td>
                <td>${category.name}</td>
                <td><img src="${category.image || 'https://via.placeholder.com/50'}" alt="${category.name}" style="width: 50px; height: 50px; object-fit: cover; border-radius: 6px;" onerror="this.src='https://via.placeholder.com/50'"></td>
                <td>${category.product_count || 0}</td>
                <td>
                    <button class="btn btn-success btn-sm" onclick="editCategory(${category.id})">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="deleteCategory(${category.id})">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

function showAddCategoryModal() {
    editingCategoryId = null;
    document.getElementById('categoryModalTitle').textContent = 'Add New Category';
    document.getElementById('categoryForm').reset();
    document.getElementById('categoryId').value = '';
    showModal('categoryModal');
}

async function editCategory(id) {
    try {
        const data = await apiRequest(`../api/categories.php?id=${id}`);
        
        if (data.success && data.data) {
            const category = data.data;
            editingCategoryId = id;
            
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
            document.getElementById('categoryId').value = category.id;
            document.getElementById('categoryName').value = category.name || '';
            document.getElementById('categoryIcon').value = category.icon || '';
            document.getElementById('categoryImage').value = category.image || '';
            document.getElementById('categoryDescription').value = category.description || '';
            
            showModal('categoryModal');
        } else {
            showMessage('Category not found', 'error');
        }
    } catch (error) {
        showMessage('Error loading category', 'error');
    }
}

async function saveCategory(e) {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const categoryData = Object.fromEntries(formData);
    
    try {
        const url = '../api/categories.php';
        const method = editingCategoryId ? 'PUT' : 'POST';
        
        const data = await apiRequest(url, {
            method: method,
            body: JSON.stringify(categoryData)
        });
        
        if (data.success) {
            showMessage(editingCategoryId ? 'Category updated successfully' : 'Category added successfully', 'success');
            hideModal('categoryModal');
            loadCategories();
        } else {
            showMessage(data.message || 'Failed to save category', 'error');
        }
    } catch (error) {
        showMessage('Error saving category', 'error');
    }
}

async function deleteCategory(id) {
    if (!confirmDelete('Are you sure you want to delete this category? All products in this category will be affected.')) {
        return;
    }
    
    try {
        const data = await apiRequest(`../api/categories.php?id=${id}`, {
            method: 'DELETE'
        });
        
        if (data.success) {
            showMessage('Category deleted successfully', 'success');
            loadCategories();
        } else {
            showMessage(data.message || 'Failed to delete category', 'error');
        }
    } catch (error) {
        showMessage('Error deleting category', 'error');
    }
}

loadCategories();
</script>

<?php require_once 'includes/footer.php'; ?>

