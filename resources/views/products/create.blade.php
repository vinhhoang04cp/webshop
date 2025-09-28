@extends('layouts.app')

@section('title', 'Add New Product')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle"></i> Add New Product
                </h5>
            </div>
            <div class="card-body">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <form id="createProductForm">
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
                        <textarea class="form-control" id="productDescription" name="description" rows="4" placeholder="Enter product description..."></textarea>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="productImage" class="form-label">Image URL</label>
                        <input type="url" class="form-control" id="productImage" name="image_url" placeholder="https://example.com/image.jpg">
                        <div class="invalid-feedback"></div>
                        <div class="form-text">Optional: Enter a URL for the product image</div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Products
                        </a>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Create Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Load categories for select dropdown
    loadCategories();
    
    $('#createProductForm').on('submit', function(e) {
        e.preventDefault();
        createProduct();
    });
});

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

function createProduct() {
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
        url: '/api/products',
        method: 'POST',
        data: formData,
        success: function(response) {
            hideLoading();
            showAlert(response.message || 'Product created successfully');
            
            // Redirect to products index after 2 seconds
            setTimeout(function() {
                window.location.href = "{{ route('products.index') }}";
            }, 2000);
        },
        error: function(xhr) {
            hideLoading();
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayValidationErrors(errors);
            } else {
                showAlert('Error creating product: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
            }
        }
    });
}

function displayValidationErrors(errors) {
    for (const field in errors) {
        const input = $(`[name="${field}"]`);
        input.addClass('is-invalid');
        input.siblings('.invalid-feedback').text(errors[field][0]);
    }
}

function clearValidationErrors() {
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
}
</script>
@endsection