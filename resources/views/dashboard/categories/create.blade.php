@extends('layouts.app')
@section('title', 'Thêm danh mục - WebShop Admin')
@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Thêm danh mục mới</h2>
                    <p class="text-muted mb-0">Tạo danh mục mới cho sản phẩm</p>
                </div>
            </div>
            @include('components.alerts')
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Thông tin danh mục</h5>
                    <a href="{{ route('dashboard.categories.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-2"></i>Quay lại</a>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dashboard.categories.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Tên danh mục <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required maxlength="150">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Mô tả</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="d-flex justify-content-end gap-2 mt-3">
                            <a href="{{ route('dashboard.categories.index') }}" class="btn btn-secondary"><i class="fas fa-times me-2"></i>Hủy</a>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Lưu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
