@extends('layouts.app')

@section('title', 'Categories Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-tags"></i> Categories Management</h1>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCreateModal()">
                <i class="bi bi-plus-circle"></i> Add Category
            </button>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Categories Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Categories</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="25%">Name</th>
                                <th width="45%">Description</th>
                                <th width="15%">Created At</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="categoriesTableBody">
                            <!-- Categories will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5" style="display: none;">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No Categories Found</h4>
                    <p class="text-muted">Start by creating your first category.</p>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="openCreateModal()">
                        <i class="bi bi-plus-circle"></i> Add First Category
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="categoryForm">
                <div class="modal-body">
                    <input type="hidden" id="categoryId" value="">
                    
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-circle"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Category Modal -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye"></i> View Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-sm-3"><strong>ID:</strong></div>
                    <div class="col-sm-9" id="viewId"></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Name:</strong></div>
                    <div class="col-sm-9" id="viewName"></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Description:</strong></div>
                    <div class="col-sm-9" id="viewDescription"></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Created At:</strong></div>
                    <div class="col-sm-9" id="viewCreatedAt"></div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-sm-3"><strong>Updated At:</strong></div>
                    <div class="col-sm-9" id="viewUpdatedAt"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-trash"></i> Delete Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this category?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    This action cannot be undone. The category "<strong id="deleteItemName"></strong>" will be permanently removed.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Load categories on page load
    loadCategories();

    // Category form submission
    $('#categoryForm').on('submit', function(e) {
        e.preventDefault();
        saveCategory();
    });

    // Delete confirmation
    $('#confirmDelete').on('click', function() {
        const categoryId = $(this).data('id');
        deleteCategory(categoryId);
    });
});

// Load all categories
function loadCategories() {
    showLoading();
    
    $.ajax({
        url: '/api/categories',
        method: 'GET',
        success: function(response) {
            hideLoading();
            displayCategories(response.data.data);
        },
        error: function(xhr) {
            hideLoading();
            showAlert('Error loading categories: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

// Display categories in table
function displayCategories(categories) {
    const tbody = $('#categoriesTableBody');
    tbody.empty();
    
    if (categories.length === 0) {
        $('#emptyState').show();
        return;
    }
    
    $('#emptyState').hide();
    
    categories.forEach(category => {
        const row = `
            <tr>
                <td>${category.id}</td>
                <td><strong>${category.name}</strong></td>
                <td>${category.description || '<em class="text-muted">No description</em>'}</td>
                <td>${category.created_at ? formatDate(category.created_at) : '<em class="text-muted">N/A</em>'}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-info" onclick="viewCategory(${category.id})" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="editCategory(${category.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDeleteCategory(${category.id}, '${category.name}')" title="Delete">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        tbody.append(row);
    });
}

// Open create modal
function openCreateModal() {
    $('#modalTitle').text('Add Category');
    $('#categoryForm')[0].reset();
    $('#categoryId').val('');
    $('#submitBtn').html('<i class="bi bi-check-circle"></i> Save Category');
    clearValidationErrors();
}

// Edit category
function editCategory(id) {
    showLoading();
    
    $.ajax({
        url: `/api/categories/${id}`,
        method: 'GET',
        success: function(response) {
            hideLoading();
            const category = response.data;
            
            $('#modalTitle').text('Edit Category');
            $('#categoryId').val(category.id);
            $('#categoryName').val(category.name);
            $('#categoryDescription').val(category.description);
            $('#submitBtn').html('<i class="bi bi-pencil"></i> Update Category');
            
            clearValidationErrors();
            $('#categoryModal').modal('show');
        },
        error: function(xhr) {
            hideLoading();
            showAlert('Error loading category: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

// View category
function viewCategory(id) {
    showLoading();
    
    $.ajax({
        url: `/api/categories/${id}`,
        method: 'GET',
        success: function(response) {
            hideLoading();
            const category = response.data;
            
            $('#viewId').text(category.id);
            $('#viewName').text(category.name);
            $('#viewDescription').text(category.description || 'No description');
            $('#viewCreatedAt').text(formatDateTime(category.created_at));
            $('#viewUpdatedAt').text(formatDateTime(category.updated_at));
            
            $('#viewCategoryModal').modal('show');
        },
        error: function(xhr) {
            hideLoading();
            showAlert('Error loading category: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

// Save category (create or update)
function saveCategory() {
    const categoryId = $('#categoryId').val();
    const isEditing = categoryId !== '';
    
    const formData = {
        name: $('#categoryName').val(),
        description: $('#categoryDescription').val()
    };
    
    showLoading();
    clearValidationErrors();
    
    $.ajax({
        url: isEditing ? `/api/categories/${categoryId}` : '/api/categories',
        method: isEditing ? 'PUT' : 'POST',
        data: formData,
        success: function(response) {
            hideLoading();
            $('#categoryModal').modal('hide');
            showAlert(response.message || (isEditing ? 'Category updated successfully' : 'Category created successfully'));
            loadCategories();
        },
        error: function(xhr) {
            hideLoading();
            if (xhr.status === 422) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                displayValidationErrors(errors);
            } else {
                showAlert('Error saving category: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
            }
        }
    });
}

// Confirm delete category
function confirmDeleteCategory(id, name) {
    $('#deleteItemName').text(name);
    $('#confirmDelete').data('id', id);
    $('#deleteModal').modal('show');
}

// Delete category
function deleteCategory(id) {
    showLoading();
    
    $.ajax({
        url: `/api/categories/${id}`,
        method: 'DELETE',
        success: function(response) {
            hideLoading();
            $('#deleteModal').modal('hide');
            showAlert(response.message || 'Category deleted successfully');
            loadCategories();
        },
        error: function(xhr) {
            hideLoading();
            $('#deleteModal').modal('hide');
            showAlert('Error deleting category: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

// Display validation errors
function displayValidationErrors(errors) {
    for (const field in errors) {
        const input = $(`[name="${field}"]`);
        input.addClass('is-invalid');
        input.siblings('.invalid-feedback').text(errors[field][0]);
    }
}

// Clear validation errors
function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}

// Format date
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN');
}

// Format datetime
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
}
</script>
@endsection