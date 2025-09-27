@extends('layouts.app')

@section('title', 'Quản lý danh mục')

@section('content')
<div class="row">
    <div class="col-12">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-tags me-2"></i>Quản lý danh mục
            </h1>
            <a href="{{ route('categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i>Thêm danh mục mới
            </a>
        </div>

        <!-- Categories Table -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>Danh sách danh mục
                </h5>
            </div>
            <div class="card-body">
                @if($categories->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th scope="col">#</th>
                                    <th scope="col">Tên danh mục</th>
                                    <th scope="col">Mô tả</th>
                                    <th scope="col" class="text-center">Số sản phẩm</th>
                                    <th scope="col" class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categories as $category)
                                    <tr>
                                        <th scope="row">{{ $category->category_id }}</th>
                                        <td>
                                            <strong>{{ $category->name }}</strong>
                                        </td>
                                        <td>
                                            @if($category->description)
                                                {{ Str::limit($category->description, 100) }}
                                            @else
                                                <em class="text-muted">Chưa có mô tả</em>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $category->products->count() }}</span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <!-- View Button -->
                                                <a href="{{ route('categories.show', $category->category_id) }}" 
                                                   class="btn btn-outline-info btn-sm"
                                                   data-bs-toggle="tooltip" 
                                                   title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                <!-- Edit Button -->
                                                <a href="{{ route('categories.edit', $category->category_id) }}" 
                                                   class="btn btn-outline-warning btn-sm"
                                                   data-bs-toggle="tooltip" 
                                                   title="Chỉnh sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <!-- Delete Button -->
                                                <form action="{{ route('categories.destroy', $category->category_id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục này? Lưu ý: Không thể xóa nếu còn sản phẩm trong danh mục.')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-outline-danger btn-sm"
                                                            data-bs-toggle="tooltip" 
                                                            title="Xóa"
                                                            @if($category->products->count() > 0) disabled @endif>
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Chưa có danh mục nào</h5>
                        <p class="text-muted">Hãy thêm danh mục đầu tiên để bắt đầu quản lý sản phẩm.</p>
                        <a href="{{ route('categories.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>Thêm danh mục mới
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Tổng danh mục</h5>
                                <h2 class="mb-0">{{ $categories->count() }}</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-tags fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Danh mục có sản phẩm</h5>
                                <h2 class="mb-0">{{ $categories->filter(fn($cat) => $cat->products->count() > 0)->count() }}</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h5 class="card-title">Danh mục trống</h5>
                                <h2 class="mb-0">{{ $categories->filter(fn($cat) => $cat->products->count() == 0)->count() }}</h2>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-exclamation-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Enable Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
</script>
@endpush
