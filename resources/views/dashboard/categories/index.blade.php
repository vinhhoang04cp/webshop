@extends('layouts.app')

@section('title', 'Danh mục - WebShop Admin')

@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 dashboard-sidebar d-flex flex-column">
            <div class="sidebar-header">
                <h3><i class="fas fa-shield-alt"></i> WebShop</h3>
                <small class="text-muted" style="color: #9ca3af !important;">Admin Panel</small>
            </div>
            
            <nav class="nav flex-column sidebar-menu">
                <a class="nav-link" href="{{ route('dashboard') }}">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a class="nav-link" href="{{ route('dashboard.products.index') }}">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
                <a class="nav-link active" href="{{ route('dashboard.categories.index') }}">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <a class="nav-link" href="#orders">
                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                </a>
                <a class="nav-link" href="#users">
                    <i class="fas fa-users"></i> Người dùng
                </a>
                <a class="nav-link" href="#reports">
                    <i class="fas fa-chart-bar"></i> Báo cáo
                </a>
            </nav>
            
            <div class="user-info mt-auto">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ auth()->user()->hasRole('admin') ? 'Administrator' : 'Manager' }}</div>
                <form method="POST" action="{{ route('logout') }}" class="mt-3">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm w-100">
                        <i class="fas fa-sign-out-alt me-2"></i> Đăng xuất
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Quản lý danh mục</h2>
                    <p class="text-muted mb-0">Quản lý danh mục sản phẩm</p>
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách danh mục</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('dashboard.categories.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm danh mục
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('dashboard.categories.index') }}" class="search-box">
                                <input name="search" class="form-control form-control-sm" 
                                       placeholder="Tìm kiếm theo tên..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                                <a href="{{ route('dashboard.categories.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i> Xóa
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th>Tên danh mục</th>
                                    <th>Mô tả</th>
                                    <th style="width: 200px;" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($error))
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-danger">
                                            <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                                            {{ $error }}
                                        </td>
                                    </tr>
                                @elseif($categories->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Không có danh mục nào
                                        </td>
                                    </tr>
                                @else
                                    @foreach($categories as $category)
                                        <tr>
                                            <td><strong>{{ $category->category_id }}</strong></td>
                                            <td>{{ $category->name }}</td>
                                            <td class="text-muted">{{ $category->description ?: '-' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('dashboard.categories.show', $category->category_id) }}" 
                                                   class="btn btn-sm btn-outline-info"
                                                   title="Xem">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('dashboard.categories.edit', $category->category_id) }}" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteCategoryModal{{ $category->category_id }}"
                                                        title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Delete Modal for each category -->
                                        <div class="modal fade" id="deleteCategoryModal{{ $category->category_id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('dashboard.categories.destroy', $category->category_id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Xóa danh mục</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Bạn có chắc chắn muốn xóa danh mục <strong>{{ $category->name }}</strong>?</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-danger">Xóa</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Laravel Pagination -->
                    @if($categories->hasPages())
                    <div class="border-top px-3 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <small>
                                    Hiển thị {{ $categories->firstItem() }}-{{ $categories->lastItem() }} 
                                    trong tổng số {{ $categories->total() }} danh mục
                                    @if(request('search'))
                                        (tìm kiếm: "{{ request('search') }}")
                                    @endif
                                </small>
                            </div>
                            <div>
                                {{ $categories->appends(request()->query())->links('pagination::bootstrap-4') }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
