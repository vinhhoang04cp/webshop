@extends('layouts.app')

@section('title', 'Chỉnh sửa danh mục')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-edit me-2"></i>Chỉnh sửa danh mục
            </h1>
            <div>
                <a href="{{ route('categories.show', $category->category_id) }}" class="btn btn-outline-info me-2">
                    <i class="fas fa-eye me-1"></i>Xem chi tiết
                </a>
                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i>Quay lại
                </a>
            </div>
        </div>

        <!-- Edit Form Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-tag me-2"></i>Thông tin danh mục: <strong>{{ $category->name }}</strong>
                </h5>
            </div>
            <div class="card-body">
                <form action="{{ route('categories.update', $category->category_id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <!-- Category Name -->
                    <div class="mb-3">
                        <label for="name" class="form-label">
                            <i class="fas fa-tag me-1"></i>Tên danh mục <span class="text-danger">*</span>
                        </label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $category->name) }}" 
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
                                  placeholder="Nhập mô tả cho danh mục...">{{ old('description', $category->description) }}</textarea>
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
                        <div>
                            <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary me-2">
                                <i class="fas fa-times me-1"></i>Hủy bỏ
                            </a>
                            <a href="{{ route('categories.show', $category->category_id) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                            </a>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-save me-1"></i>Cập nhật danh mục
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Category Info Card -->
        <div class="card mt-4 border-info">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>Thông tin danh mục
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> {{ $category->category_id }}</p>
                        <p><strong>Tên hiện tại:</strong> {{ $category->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Số sản phẩm:</strong> 
                            <span class="badge bg-info">{{ $category->products->count() }}</span>
                        </p>
                        @if($category->products->count() > 0)
                            <p><strong>Trạng thái:</strong> 
                                <span class="badge bg-success">Đang sử dụng</span>
                            </p>
                        @else
                            <p><strong>Trạng thái:</strong> 
                                <span class="badge bg-warning">Chưa có sản phẩm</span>
                            </p>
                        @endif
                    </div>
                </div>
                
                @if($category->products->count() > 0)
                    <div class="mt-3">
                        <h6>Sản phẩm trong danh mục:</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($category->products->take(5) as $product)
                                <span class="badge bg-secondary">{{ $product->name }}</span>
                            @endforeach
                            @if($category->products->count() > 5)
                                <span class="badge bg-light text-dark">+{{ $category->products->count() - 5 }} khác</span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Instructions Card -->
        <div class="card mt-4 border-warning">
            <div class="card-header bg-warning text-dark">
                <h6 class="mb-0">
                    <i class="fas fa-exclamation-triangle me-2"></i>Lưu ý khi chỉnh sửa
                </h6>
            </div>
            <div class="card-body">
                <ul class="mb-0">
                    <li>Thay đổi tên danh mục có thể ảnh hưởng đến cách hiển thị sản phẩm.</li>
                    <li>Danh mục này hiện có <strong>{{ $category->products->count() }}</strong> sản phẩm.</li>
                    @if($category->products->count() > 0)
                        <li class="text-warning">⚠️ Không thể xóa danh mục này vì còn có sản phẩm.</li>
                    @else
                        <li class="text-success">✅ Có thể xóa danh mục này vì chưa có sản phẩm nào.</li>
                    @endif
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
    
    // Form validation and change detection
    const form = document.querySelector('form');
    const originalData = {
        name: document.getElementById('name').value,
        description: document.getElementById('description').value
    };
    
    // Check if form has changes
    function hasChanges() {
        return document.getElementById('name').value !== originalData.name ||
               document.getElementById('description').value !== originalData.description;
    }
    
    // Warn user about unsaved changes
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges()) {
            const message = 'Bạn có thay đổi chưa được lưu. Bạn có chắc chắn muốn rời khỏi trang?';
            e.returnValue = message;
            return message;
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        const name = document.getElementById('name').value.trim();
        
        if (name.length < 2) {
            e.preventDefault();
            alert('Tên danh mục phải có ít nhất 2 ký tự.');
            document.getElementById('name').focus();
            return false;
        }
        
        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Đang cập nhật...';
        submitBtn.disabled = true;
        
        // Remove beforeunload warning
        window.removeEventListener('beforeunload', arguments.callee);
    });
    
    // Remove warning when clicking internal links
    document.querySelectorAll('a[href^="/"], a[href^="' + window.location.origin + '"]').forEach(link => {
        link.addEventListener('click', function() {
            window.removeEventListener('beforeunload', arguments.callee);
        });
    });
</script>
@endpush
