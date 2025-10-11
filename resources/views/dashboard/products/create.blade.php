@extends('layouts.app')
@section('title', 'Thêm sản phẩm - WebShop Admin')
@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Thêm sản phẩm mới</h2>
                    <p class="text-muted mb-0">Tạo sản phẩm mới cho cửa hàng</p>
                </div>
            </div>
            @include('components.alerts')

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Thông tin sản phẩm</h5>
                    <a href="{{ route('dashboard.products.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dashboard.products.store') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="255">
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả sản phẩm</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="image_url" class="form-label">URL hình ảnh</label>
                                    <input type="url" class="form-control @error('image_url') is-invalid @enderror" id="image_url" name="image_url" value="{{ old('image_url') }}">
                                    @error('image_url')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="price" class="form-label">Giá (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" id="price" name="price" value="{{ old('price') }}" required min="0" step="1000">
                                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                        <option value="">Chọn danh mục</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->category_id }}" {{ old('category_id') == $category->category_id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Xem trước</label>
                                    <div class="border rounded p-3 text-center bg-light">
                                        <img id="preview" src="" alt="Preview" class="img-fluid rounded" style="max-height:150px;display:none">
                                        <div id="no-image" class="text-muted"><i class="fas fa-image fa-2x mb-2"></i><p>Nhập URL để xem</p></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('dashboard.products.index') }}" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Hủy</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
const imageUrlInput = document.getElementById('image_url');
const preview = document.getElementById('preview');
const noImage = document.getElementById('no-image');
imageUrlInput.addEventListener('input', function() {
    const url = this.value.trim();
    if (url) {
        preview.src = url;
        preview.style.display = 'block';
        noImage.style.display = 'none';
        preview.onerror = function() {
            preview.style.display = 'none';
            noImage.innerHTML = '<i class="fas fa-exclamation-triangle fa-2x mb-2 text-warning"></i><p>Không thể tải ảnh</p>';
            noImage.style.display = 'block';
        };
    } else {
        preview.style.display = 'none';
        noImage.innerHTML = '<i class="fas fa-image fa-2x mb-2"></i><p>Nhập URL để xem</p>';
        noImage.style.display = 'block';
    }
});
</script>
@endsection