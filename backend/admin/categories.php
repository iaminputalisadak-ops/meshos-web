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
    
    <div style="margin-bottom: 20px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
        <button class="btn btn-secondary" onclick="loadCategories()" title="Refresh Categories" id="refreshBtn">
            <i class="fas fa-sync-alt"></i> Refresh
        </button>
        <a href="seed_categories.php" class="btn btn-secondary" target="_blank">
            <i class="fas fa-seedling"></i> Seed Default Categories
        </a>
    </div>
    
    <!-- Fixed Total Count Container -->
    <div id="totalCountContainer" style="margin-bottom: 20px; padding: 10px; background: #f8f9fa; border-radius: 8px; min-height: 45px; display: flex; align-items: center;">
        <p style="margin: 0;"><strong>Total Categories:</strong> <span id="totalCount" style="color: var(--primary-color); font-size: 18px; min-width: 30px; display: inline-block;">—</span></p>
    </div>
    
    <div class="table-wrapper">
        <div id="categoriesTable">
            <p class="loading-text">Loading categories...</p>
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
        const container = document.getElementById('categoriesTable');
        const refreshBtn = document.getElementById('refreshBtn');
        const totalCountEl = document.getElementById('totalCount');
        
        // Save scroll position to prevent jumping
        const scrollPosition = window.pageYOffset || document.documentElement.scrollTop;
        
        // Disable refresh button and show loading
        if (refreshBtn) {
            refreshBtn.disabled = true;
            refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        }
        
        // Keep the container height stable to prevent jumping
        const currentHeight = container.offsetHeight;
        if (currentHeight > 0) {
            container.style.minHeight = currentHeight + 'px';
        }
        
        container.innerHTML = '<p class="loading-text">Loading categories...</p>';
        if (totalCountEl) {
            totalCountEl.textContent = '—';
        }
        
        const data = await apiRequest('../api/categories.php');
        
        // Remove min-height after loading
        container.style.minHeight = '';
        
        if (data.success) {
            displayCategories(data.data || []);
        } else {
            container.innerHTML = '<div class="empty-state"><i class="fas fa-tags"></i><h3>No Categories</h3><p>Add your first category to get started</p></div>';
            if (totalCountEl) {
                totalCountEl.textContent = '0';
            }
            showMessage(data.message || 'Failed to load categories', 'error');
        }
        
        // Restore scroll position after a brief delay to allow DOM to update
        setTimeout(() => {
            window.scrollTo(0, scrollPosition);
        }, 50);
        
    } catch (error) {
        const container = document.getElementById('categoriesTable');
        const totalCountEl = document.getElementById('totalCount');
        container.style.minHeight = '';
        container.innerHTML = '<div class="empty-state"><i class="fas fa-tags"></i><h3>No Categories</h3><p>Add your first category to get started</p></div>';
        if (totalCountEl) {
            totalCountEl.textContent = '0';
        }
        showMessage('Error loading categories', 'error');
    } finally {
        // Re-enable refresh button
        const refreshBtn = document.getElementById('refreshBtn');
        if (refreshBtn) {
            refreshBtn.disabled = false;
            refreshBtn.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh';
        }
    }
}

function displayCategories(categories) {
    const container = document.getElementById('categoriesTable');
    const totalCountEl = document.getElementById('totalCount');
    
    // Update total count in fixed container (smooth transition)
    if (totalCountEl) {
        totalCountEl.style.opacity = '0.5';
        setTimeout(() => {
            totalCountEl.textContent = categories.length;
            totalCountEl.style.opacity = '1';
        }, 100);
    }
    
    if (categories.length === 0) {
        container.innerHTML = '<div class="empty-state"><i class="fas fa-tags"></i><h3>No Categories</h3><p>Add your first category to get started</p></div>';
        return;
    }
    
    // Create a nice table with all category details (responsive)
    // Note: Total count is now in a separate fixed container above
    let html = `
        <div class="table-responsive">
            <table class="categories-table">
                <thead>
                    <tr>
                        <th class="col-id">ID</th>
                        <th class="col-icon">Icon</th>
                        <th class="col-name">Category Name</th>
                        <th class="col-slug">Slug</th>
                        <th class="col-image">Image</th>
                        <th class="col-products">Products</th>
                        <th class="col-created">Created</th>
                        <th class="col-actions">Actions</th>
                    </tr>
                </thead>
                <tbody>
    `;
    
    categories.forEach((category, index) => {
        const createdDate = category.created_at ? new Date(category.created_at).toLocaleDateString() : 'N/A';
        const productCount = category.product_count || 0;
        const rowClass = index % 2 === 0 ? 'even-row' : 'odd-row';
        
        html += `
            <tr class="${rowClass}">
                <td class="col-id">
                    <span class="category-id">#${category.id}</span>
                </td>
                <td class="col-icon">
                    <div class="category-icon">${category.icon || '📦'}</div>
                </td>
                <td class="col-name">
                    <div class="category-name-wrapper">
                        <strong class="category-name">${category.name}</strong>
                        ${category.description ? `<small class="category-desc">${category.description.substring(0, 50)}${category.description.length > 50 ? '...' : ''}</small>` : ''}
                    </div>
                </td>
                <td class="col-slug">
                    <code class="category-slug">${category.slug || '—'}</code>
                </td>
                <td class="col-image">
                    <div class="category-image-wrapper">
                        <img src="${category.image || 'https://via.placeholder.com/60'}" 
                             alt="${category.name}" 
                             class="category-image"
                             onerror="this.src='https://via.placeholder.com/60'">
                    </div>
                </td>
                <td class="col-products">
                    <span class="badge ${productCount > 0 ? 'badge-success' : 'badge-warning'} product-count-badge">
                        <i class="fas fa-box"></i> ${productCount}
                    </span>
                </td>
                <td class="col-created">
                    <span class="created-date">${createdDate}</span>
                </td>
                <td class="col-actions">
                    <div class="action-buttons">
                        <button class="btn btn-success btn-sm btn-action" onclick="editCategory(${category.id})" title="Edit Category">
                            <i class="fas fa-edit"></i> <span class="btn-text">Edit</span>
                        </button>
                        <button class="btn btn-danger btn-sm btn-action" onclick="deleteCategory(${category.id})" title="Delete Category">
                            <i class="fas fa-trash"></i> <span class="btn-text">Delete</span>
                        </button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
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
        const response = await fetch(`../api/categories.php?id=${id}`);
        const data = await response.json();
        
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

<style>
/* Responsive Categories Table */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.categories-table {
    width: 100%;
    min-width: 800px;
}

/* Prevent layout shift during refresh */
#totalCountContainer {
    transition: none;
    will-change: auto;
}

#totalCount {
    transition: opacity 0.2s ease;
    text-align: center;
    min-width: 30px;
    display: inline-block;
}

/* Prevent layout shift */
#totalCountContainer {
    position: relative;
    overflow: hidden;
}

.category-image-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
}

.category-image {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 10px;
    border: 2px solid #e0e0e0;
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.category-image:hover {
    transform: scale(1.15);
    cursor: pointer;
    border-color: var(--primary-color);
    box-shadow: 0 4px 8px rgba(244, 51, 151, 0.3);
}

.category-icon {
    font-size: 28px;
    text-align: center;
    padding: 5px;
}

.category-id {
    background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
    color: white;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 12px;
    display: inline-block;
}

.category-name-wrapper {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.category-name {
    color: var(--dark-color);
    font-size: 15px;
}

.category-desc {
    color: #666;
    font-size: 12px;
    font-style: italic;
}

.category-slug {
    background: linear-gradient(135deg, #f4f4f4, #e9e9e9);
    padding: 6px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-family: 'Courier New', monospace;
    border: 1px solid #ddd;
}

.product-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    font-weight: 600;
}

.created-date {
    color: #666;
    font-size: 12px;
    white-space: nowrap;
}

.btn-action {
    transition: all 0.3s;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.categories-table tbody tr {
    transition: all 0.3s;
}

.categories-table tbody tr:hover {
    background: linear-gradient(90deg, rgba(244, 51, 151, 0.05), rgba(102, 126, 234, 0.05));
    transform: scale(1.01);
}

.categories-table tbody tr.even-row {
    background: #fafafa;
}

.categories-table tbody tr.odd-row {
    background: white;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.action-buttons .btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-text {
    display: inline;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .categories-table {
        min-width: 600px;
    }
    
    .col-slug,
    .col-created {
        display: none;
    }
    
    .action-buttons {
        flex-direction: column;
        gap: 3px;
    }
    
    .action-buttons .btn-sm {
        width: 100%;
        padding: 8px;
    }
    
    .section-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .section-header .btn {
        width: 100%;
        margin-top: 10px;
    }
}

@media (max-width: 480px) {
    .categories-table {
        min-width: 500px;
    }
    
    .col-icon,
    .col-image {
        display: none;
    }
    
    .col-name {
        min-width: 150px;
    }
    
    .btn-text {
        display: none;
    }
    
    .action-buttons .btn-sm {
        padding: 8px;
        min-width: 40px;
    }
    
    .action-buttons .btn-sm i {
        margin: 0;
    }
}
</style>

<?php require_once 'includes/footer.php'; ?>

