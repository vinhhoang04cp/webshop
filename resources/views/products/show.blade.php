@extends('layouts.app')

@section('title', 'Product Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-eye"></i> Product Details
                </h5>
                <div>
                    <button type="button" class="btn btn-warning btn-sm" onclick="editProduct()">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteProduct()">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Product Details -->
                <div id="productDetails">
                    <div class="row">
                        <div class="col-md-4" id="productImageContainer" style="display: none;">
                            <img id="productImage" class="img-fluid rounded mb-3" alt="Product Image">
                        </div>
                        <div class="col-md-8">
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>ID:</strong></div>
                                <div class="col-sm-9" id="productId"></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Name:</strong></div>
                                <div class="col-sm-9" id="productName"></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Price:</strong></div>
                                <div class="col-sm-9" id="productPrice"></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Category:</strong></div>
                                <div class="col-sm-9" id="productCategory"></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Stock Quantity:</strong></div>
                                <div class="col-sm-9" id="productStock"></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Description:</strong></div>
                                <div class="col-sm-9" id="productDescription"></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Created At:</strong></div>
                                <div class="col-sm-9" id="productCreatedAt"></div>
                            </div>
                            <hr>
                            <div class="row mb-3">
                                <div class="col-sm-3"><strong>Updated At:</strong></div>
                                <div class="col-sm-9" id="productUpdatedAt"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-start">
                    <a href="{{ route('products.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Products
                    </a>
                </div>
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
                    This action cannot be undone. The product will be permanently removed.
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
let currentProduct = null;

$(document).ready(function() {
    loadProduct();
    
    $('#confirmDelete').on('click', function() {
        deleteProduct();
    });
});

function loadProduct() {
    const productId = {{ $id }};
    showLoading();
    
    $.ajax({
        url: `/api/products/${productId}`,
        method: 'GET',
        success: function(response) {
            hideLoading();
            currentProduct = response.data;
            displayProduct(currentProduct);
        },
        error: function(xhr) {
            hideLoading();
            if (xhr.status === 404) {
                showAlert('Product not found', 'danger');
                setTimeout(function() {
                    window.location.href = "{{ route('products.index') }}";
                }, 2000);
            } else {
                showAlert('Error loading product: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
            }
        }
    });
}

function displayProduct(product) {
    $('#productId').text(product.id);
    $('#productName').html(`<h4 class="text-primary">${product.name}</h4>`);
    $('#productDescription').html(product.description || '<em class="text-muted">No description</em>');
    $('#productPrice').html(`<span class="h5 text-success">$${parseFloat(product.price).toFixed(2)}</span>`);
    $('#productCategory').html(product.category ? 
        `<span class="badge bg-secondary fs-6">${product.category.name}</span>` : 
        '<em class="text-muted">No category</em>');
    
    // Stock with color coding
    const stockBadge = product.stock_quantity > 0 ? 
        `<span class="badge bg-success fs-6">${product.stock_quantity} in stock</span>` :
        '<span class="badge bg-danger fs-6">Out of stock</span>';
    $('#productStock').html(stockBadge);
    
    $('#productCreatedAt').text(formatDateTime(product.created_at));
    $('#productUpdatedAt').text(formatDateTime(product.updated_at));
    
    // Handle image
    if (product.image_url) {
        $('#productImage').attr('src', product.image_url);
        $('#productImageContainer').show();
    } else {
        $('#productImageContainer').hide();
    }
}

function editProduct() {
    if (currentProduct) {
        window.location.href = `/products/${currentProduct.id}/edit`;
    }
}

function confirmDeleteProduct() {
    $('#deleteModal').modal('show');
}

function deleteProduct() {
    if (!currentProduct) return;
    
    showLoading();
    
    $.ajax({
        url: `/api/products/${currentProduct.id}`,
        method: 'DELETE',
        success: function(response) {
            hideLoading();
            $('#deleteModal').modal('hide');
            showAlert(response.message || 'Product deleted successfully');
            
            // Redirect to products index after 2 seconds
            setTimeout(function() {
                window.location.href = "{{ route('products.index') }}";
            }, 2000);
        },
        error: function(xhr) {
            hideLoading();
            $('#deleteModal').modal('hide');
            showAlert('Error deleting product: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
}
</script>
@endsection