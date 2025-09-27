@extends('layouts.app')

@section('title', 'Thêm danh mục mới')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-plus me-2"></i>Thêm danh mục mới
            </h1>
            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Quay lại
            </a>
        </div>

        <!-- Create Form Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-edit me-2"></i>Thông tin danh mục
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    
                    <!-- Category Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag me-1"></i>Tên danh mục <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               placeholder="Nhập tên danh mục..."
                               required>
                        @error('name')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            Tên danh mục phải duy nhất và dễ hiểu.
                        </div>
                    </div>

                    <!-- Category Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label">
                            <i class="fas fa-align-left me-1"></i>Mô tả
                        </label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" 
                                  name="description" 
                                  rows="4" 
                                  placeholder="Nhập mô tả cho danh mục...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            Mô tả chi tiết về danh mục sản phẩm (không bắt buộc).
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Hủy bỏ
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Lưu danh mục
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="card mt-4 border-info">
            <div class="card-header bg-info text-white">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Hướng dẫn
                </h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Tên danh mục là bắt buộc và phải duy nhất.</li>
                    <li>Mô tả giúp khách hàng hiểu rõ hơn về loại sản phẩm trong danh mục.</li>
                    <li>Sau khi tạo danh mục, bạn có thể thêm sản phẩm vào danh mục này.</li>
                    <li>Danh mục có thể được chỉnh sửa hoặc xóa nếu không còn sản phẩm nào.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-focus on name field when page loads
    document.getElementById('name').focus();
    
    // Character counter for description
    const descriptionField = document.getElementById('description');
    const maxLength = 500;
    
    // Create character counter element
    const counterElement = document.createElement('div');
    counterElement.className = 'form-text text-end';
    counterElement.id = 'description-counter';
    descriptionField.parentNode.appendChild(counterElement);
    
    // Update counter function
    function updateCounter() {
        const currentLength = descriptionField.value.length;
        counterElement.textContent = `${currentLength}/${maxLength} ký tự`;
        
        if (currentLength > maxLength * 0.9) {
            counterElement.className = 'form-text text-end text-warning';
        } else {
            counterElement.className = 'form-text text-end text-muted';
        }
    }
    
    // Initialize counter
    updateCounter();
    
    // Update counter on input
    descriptionField.addEventListener('input', updateCounter);
    
    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        
        if (name.length < 2) {
            e.preventDefault();
            alert('Tên danh mục phải có ít nhất 2 ký tự.');
            document.getElementById('name').focus();
            return false;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...';
        submitBtn.disabled = true;
    });
</script>
@endpush
