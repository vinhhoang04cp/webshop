@extends('layouts.app')

@section('title', 'Category Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="bi bi-eye"></i> Category Details
                </h5>
                <div>
                    <button type="button" class="btn btn-warning btn-sm" onclick="editCategory()">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmDeleteCategory()">
                        <i class="bi bi-trash"></i> Delete
                    </button>
                </div>
            </div>
            <div class="card-body">
                <!-- Alert Container -->
                <div id="alertContainer"></div>

                <!-- Category Details -->
                <div id="categoryDetails">
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>ID:</strong></div>
                        <div class="col-sm-9" id="categoryId"></div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Name:</strong></div>
                        <div class="col-sm-9" id="categoryName"></div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Description:</strong></div>
                        <div class="col-sm-9" id="categoryDescription"></div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Created At:</strong></div>
                        <div class="col-sm-9" id="categoryCreatedAt"></div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-sm-3"><strong>Updated At:</strong></div>
                        <div class="col-sm-9" id="categoryUpdatedAt"></div>
                    </div>
                </div>

                <div class="d-flex justify-content-start">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> Back to Categories
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
                <h5 class="modal-title"><i class="bi bi-trash"></i> Delete Category</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this category?</p>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    This action cannot be undone. The category will be permanently removed.
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
let currentCategory = null;

$(document).ready(function() {
    loadCategory();
    
    $('#confirmDelete').on('click', function() {
        deleteCategory();
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
            currentCategory = response.data;
            displayCategory(currentCategory);
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

function displayCategory(category) {
    $('#categoryId').text(category.id);
    $('#categoryName').text(category.name);
    $('#categoryDescription').text(category.description || 'No description');
    $('#categoryCreatedAt').text(formatDateTime(category.created_at));
    $('#categoryUpdatedAt').text(formatDateTime(category.updated_at));
}

function editCategory() {
    if (currentCategory) {
        window.location.href = `/categories/${currentCategory.id}/edit`;
    }
}

function confirmDeleteCategory() {
    $('#deleteModal').modal('show');
}

function deleteCategory() {
    const categoryId = "{{ $id }}";
    showLoading();
    
    $.ajax({
        url: `/api/categories/${categoryId}`,
        method: 'DELETE',
        success: function(response) {
            hideLoading();
            $('#deleteModal').modal('hide');
            showAlert(response.message || 'Category deleted successfully');
            
            // Redirect to categories index after 2 seconds
            setTimeout(function() {
                window.location.href = "{{ route('categories.index') }}";
            }, 2000);
        },
        error: function(xhr) {
            hideLoading();
            $('#deleteModal').modal('hide');
            showAlert('Error deleting category: ' + (xhr.responseJSON?.message || 'Unknown error'), 'danger');
        }
    });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('vi-VN') + ' ' + date.toLocaleTimeString('vi-VN');
}
</script>
@endsection