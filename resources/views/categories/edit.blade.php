@extends('layouts.app')

@section('title', 'Edit Category')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="bi bi-pencil"></i> Edit Category
                </h5>
            </div>
            <div class="card-body">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <form id="editCategoryForm">
                    <div class="mb-3">
                        <label for="categoryName" class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="categoryName" name="name" required>
                        <div class="invalid-feedback"></div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="categoryDescription" class="form-label">Description</label>
                        <textarea class="form-control" id="categoryDescription" name="description" rows="4" placeholder="Enter category description..."></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left"></i> Back to Categories
                        </a>
                        <div>
                            <a href="{{ route('categories.show', $id) }}" class="btn btn-info me-2">
                                <i class="bi bi-eye"></i> View
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle"></i> Update Category
                            </button>
                        </div>
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
    loadCategory();
    
    $('#editCategoryForm').on('submit', function(e) {
        e.preventDefault();
        updateCategory();
    });
});

function loadCategory() {
    const categoryId = "{{ $id }}";
    showLoading();
    
    $.ajax({
        url: `/api/categories/${categoryId}`,
        method: 'GET',
        success: function(response) {
            hideLoading();
            const category = response.data;
            
            $('#categoryName').val(category.name);
            $('#categoryDescription').val(category.description);
        },
        error: function(xhr) {
            hideLoading();
            if (xhr.status === 404) {
                showAlert('Category not found', 'danger');
            } else {
                showAlert('Error loading category: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
            }
        }
    });
}

function updateCategory() {
    const categoryId = "{{ $id }}";
    const formData = {
        name: $('#categoryName').val(),
        description: $('#categoryDescription').val()
    };
    
    showLoading();
    clearValidationErrors();
    
    $.ajax({
        url: `/api/categories/${categoryId}`,
        method: 'PUT',
        data: formData,
        success: function(response) {
            hideLoading();
            showAlert(response.message || 'Category updated successfully');
            
            // Redirect to categories index after 2 seconds
            setTimeout(function() {
                window.location.href = "{{ route('categories.index') }}";
            }, 2000);
        },
        error: function(xhr) {
            hideLoading();
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                displayValidationErrors(errors);
            } else {
                showAlert('Error updating category: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
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