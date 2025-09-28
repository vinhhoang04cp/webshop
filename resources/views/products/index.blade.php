@extends('layouts.app')

@section('title', 'Products Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="bi bi-box-seam"></i> Products Management</h1>
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openCreateModal()">
                <i class="bi bi-plus-circle"></i> Add Product
            </button>
        </div>

        <!-- Alert Container -->
        <div id="alertContainer"></div>

        <!-- Products Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-list-ul"></i> All Products</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th width="5%">ID</th>
                                <th width="15%">Name</th>
                                <th width="20%">Description</th>
                                <th width="10%">Price</th>
                                <th width="10%">Category</th>
                                <th width="10%">Stock</th>
                                <th width="15%">Image</th>
                                <th width="10%">Created At</th>
                                <th width="10%">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="productsTableBody">
                            <!-- Products will be loaded here via AJAX -->
                        </tbody>
                    </table>
                </div>
                
                <!-- Empty State -->
                <div id="emptyState" class="text-center py-5" style="display: none;">
                    <i class="bi bi-inbox display-1 text-muted"></i>
                    <h4 class="text-muted mt-3">No Products Found</h4>
                    <p class="text-muted">Start by creating your first product.</p>
                    <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#productModal" onclick="openCreateModal()">
                        <i class="bi bi-plus-circle"></i> Add First Product
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Product Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Add Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="productForm">
                <div class="modal-body">
                    <input type="hidden" id="productId" value="">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productName" class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="productName" name="name" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productPrice" class="form-label">Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="productPrice" name="price" step="0.01" min="0" required>
                                </div>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productCategory" class="form-label">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="productCategory" name="category_id" required>
                                    <option value="">Select Category</option>
                                </select>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="productStock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="productStock" name="stock_quantity" min="0" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="productDescription" name="description" rows="3"></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productImage" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="productImage" name="image_url" placeholder="https://example.com/image.jpg">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="bi bi-check-circle"></i> Save Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Product Modal -->
<div class="modal fade" id="viewProductModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-eye"></i> View Product</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4" id="viewImageContainer" style="display: none;">
                        <img id="viewImage" class="img-fluid rounded mb-3" alt="Product Image">
                    </div>
                    <div class="col-md-8">
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong>ID:</strong></div>
                            <div class="col-sm-9" id="viewId"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong>Name:</strong></div>
                            <div class="col-sm-9" id="viewName"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong>Price:</strong></div>
                            <div class="col-sm-9" id="viewPrice"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong>Category:</strong></div>
                            <div class="col-sm-9" id="viewCategory"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong>Stock:</strong></div>
                            <div class="col-sm-9" id="viewStock"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong>Description:</strong></div>
                            <div class="col-sm-9" id="viewDescription"></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-sm-3"><strong>Created At:</strong></div>
                            <div class="col-sm-9" id="viewCreatedAt"></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-3"><strong>Updated At:</strong></div>
                            <div class="col-sm-9" id="viewUpdatedAt"></div>
                        </div>
                    </div>
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
                <h5 class="modal-title"><i class="bi bi-trash"></i> Delete Product</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this product?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    This action cannot be undone. The product "<strong id="deleteItemName"></strong>" will be permanently removed.
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
    // Load products and categories on page load
    loadProducts();
    loadCategories();

    // Product form submission
    $('#productForm').on('submit', function(e) {
        e.preventDefault();
        saveProduct();
    });

    // Delete confirmation
    $('#confirmDelete').on('click', function() {
        const productId = $(this).data('id');
        deleteProduct(productId);
    });
});

// Load all products
function loadProducts() {
    showLoading();
    
    $.ajax({
        url: '/api/products',
        method: 'GET',
        success: function(response) {
            hideLoading();
            displayProducts(response.data);
        },
        error: function(xhr) {
            hideLoading();
            showAlert('Error loading products: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

// Load categories for select dropdown
function loadCategories() {
    $.ajax({
        url: '/api/categories',
        method: 'GET',
        success: function(response) {
            const select = $('#productCategory');
            select.find('option:not(:first)').remove();
            
            const categories = response.data.data || response.data;
            categories.forEach(category => {
                select.append(`<option value="${category.id}">${category.name}</option>`);
            });
        },
        error: function(xhr) {
            showAlert('Error loading categories: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

// Display products in table
function displayProducts(products) {
    const tbody = $('#productsTableBody');
    tbody.empty();
    
    if (products.length === 0) {
        $('#emptyState').show();
        return;
    }
    
    $('#emptyState').hide();
    
    products.forEach(product => {
        const imageCell = product.image_url ? 
            `<img src="${product.image_url}" alt="${product.name}" class="img-thumbnail" style="max-width: 50px; max-height: 50px;">` :
            '<em class="text-muted">No image</em>';
        
        const row = `
            <tr>
                <td>${product.id}</td>
                <td><strong>${product.name}</strong></td>
                <td>${product.description || '<em class="text-muted">No description</em>'}</td>
                <td><strong>$${parseFloat(product.price).toFixed(2)}</strong></td>
                <td><span class="badge bg-secondary">${product.category ? product.category.name : 'No category'}</span></td>
                <td><span class="badge ${product.stock_quantity > 0 ? 'bg-success' : 'bg-danger'}">${product.stock_quantity}</span></td>
                <td>${imageCell}</td>
                <td>${product.created_at ? formatDate(product.created_at) : '<em class="text-muted">N/A</em>'}</td>
                <td>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-info" onclick="viewProduct(${product.id})" title="View">
                            <i class="bi bi-eye"></i>
                        </button>
                        <button type="button" class="btn btn-outline-warning" onclick="editProduct(${product.id})" title="Edit">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger" onclick="confirmDeleteProduct(${product.id}, '${product.name}')" title="Delete">
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
    $('#modalTitle').text('Add Product');
    $('#productForm')[0].reset();
    $('#productId').val('');
    $('#submitBtn').html('<i class="bi bi-check-circle"></i> Save Product');
    clearValidationErrors();
}

// Edit product
function editProduct(id) {
    showLoading();
    
    $.ajax({
        url: `/api/products/${id}`,
        method: 'GET',
        success: function(response) {
            hideLoading();
            const product = response.data;
            
            $('#modalTitle').text('Edit Product');
            $('#productId').val(product.id);
            $('#productName').val(product.name);
            $('#productDescription').val(product.description);
            $('#productPrice').val(product.price);
            $('#productCategory').val(product.category_id);
            $('#productStock').val(product.stock_quantity);
            $('#productImage').val(product.image_url);
            $('#submitBtn').html('<i class="bi bi-pencil"></i> Update Product');
            
            clearValidationErrors();
            $('#productModal').modal('show');
        },
        error: function(xhr) {
            hideLoading();
            showAlert('Error loading product: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

// View product
function viewProduct(id) {
    showLoading();
    
    $.ajax({
        url: `/api/products/${id}`,
        method: 'GET',
        success: function(response) {
            hideLoading();
            const product = response.data;
            
            $('#viewId').text(product.id);
            $('#viewName').text(product.name);
            $('#viewDescription').text(product.description || 'No description');
            $('#viewPrice').text('$' + parseFloat(product.price).toFixed(2));
            $('#viewCategory').text(product.category ? product.category.name : 'No category');
            $('#viewStock').text(product.stock_quantity);
            $('#viewCreatedAt').text(formatDateTime(product.created_at));
            $('#viewUpdatedAt').text(formatDateTime(product.updated_at));
            
            // Handle image
            if (product.image_url) {
                $('#viewImage').attr('src', product.image_url);
                $('#viewImageContainer').show();
            } else {
                $('#viewImageContainer').hide();
            }
            
            $('#viewProductModal').modal('show');
        },
        error: function(xhr) {
            hideLoading();
            showAlert('Error loading product: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

// Save product (create or update)
function saveProduct() {
    const productId = $('#productId').val();
    const isEditing = productId !== '';
    
    const formData = {
        name: $('#productName').val(),
        description: $('#productDescription').val(),
        price: $('#productPrice').val(),
        category_id: $('#productCategory').val(),
        stock_quantity: $('#productStock').val(),
        image_url: $('#productImage').val()
    };
    
    showLoading();
    clearValidationErrors();
    
    $.ajax({
        url: isEditing ? `/api/products/${productId}` : '/api/products',
        method: isEditing ? 'PUT' : 'POST',
        data: formData,
        success: function(response) {
            hideLoading();
            $('#productModal').modal('hide');
            showAlert(response.message || (isEditing ? 'Product updated successfully' : 'Product created successfully'));
            loadProducts();
        },
        error: function(xhr) {
            hideLoading();
            if (xhr.status === 422) {
                // Validation errors
                const errors = xhr.responseJSON.errors;
                displayValidationErrors(errors);
            } else {
                showAlert('Error saving product: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
            }
        }
    });
}

// Confirm delete product
function confirmDeleteProduct(id, name) {
    $('#deleteItemName').text(name);
    $('#confirmDelete').data('id', id);
    $('#deleteModal').modal('show');
}

// Delete product
function deleteProduct(id) {
    showLoading();
    
    $.ajax({
        url: `/api/products/${id}`,
        method: 'DELETE',
        success: function(response) {
            hideLoading();
            $('#deleteModal').modal('hide');
            showAlert(response.message || 'Product deleted successfully');
            loadProducts();
        },
        error: function(xhr) {
            hideLoading();
            $('#deleteModal').modal('hide');
            showAlert('Error deleting product: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
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