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
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                                <i class="fas fa-plus me-2"></i>Thêm danh mục
                            </button>
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
                                @elseif(empty($paginatedCategories))
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            Không có danh mục nào
                                        </td>
                                    </tr>
                                @else
                                    @foreach($paginatedCategories as $category)
                                        <tr>
                                            <td><strong>{{ $category['id'] }}</strong></td>
                                            <td>{{ $category['name'] }}</td>
                                            <td class="text-muted">{{ $category['description'] ?: '-' }}</td>
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-secondary" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editCategoryModal{{ $category['id'] }}"
                                                        title="Sửa">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#deleteCategoryModal{{ $category['id'] }}"
                                                        title="Xóa">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <!-- Edit Modal for each category -->
                                        <div class="modal fade" id="editCategoryModal{{ $category['id'] }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('dashboard.categories.update', $category['id']) }}">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Sửa danh mục</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label for="editName{{ $category['id'] }}" class="form-label">Tên</label>
                                                                <input type="text" 
                                                                       class="form-control @error('name') is-invalid @enderror" 
                                                                       id="editName{{ $category['id'] }}" 
                                                                       name="name" 
                                                                       value="{{ old('name', $category['name']) }}" 
                                                                       required 
                                                                       maxlength="150">
                                                                @error('name')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="editDescription{{ $category['id'] }}" class="form-label">Mô tả</label>
                                                                <textarea class="form-control" 
                                                                          id="editDescription{{ $category['id'] }}" 
                                                                          name="description">{{ old('description', $category['description']) }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                            <button type="submit" class="btn btn-primary">Cập nhật</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Delete Modal for each category -->
                                        <div class="modal fade" id="deleteCategoryModal{{ $category['id'] }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('dashboard.categories.destroy', $category['id']) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Xóa danh mục</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Bạn có chắc chắn muốn xóa danh mục <strong>{{ $category['name'] }}</strong>?</p>
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
                    
                    <!-- Detailed pagination info and controls -->
                    @if(!empty($paginatedCategories) && isset($paginationInfo))
                    <div class="border-top px-3 py-3">
                        <!-- Pagination Info -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <small>
                                    Hiển thị {{ $paginationInfo['startItem'] }}-{{ $paginationInfo['endItem'] }} trong tổng số {{ $paginationInfo['totalItems'] }} danh mục
                                    @if(request('search'))
                                        (tìm kiếm: "{{ request('search') }}")
                                    @endif
                                </small>
                            </div>
                            
                            <!-- Pagination Controls -->
                            @if($paginationInfo['totalPages'] > 1)
                            <nav aria-label="Category pagination">
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Previous Button -->
                                    <li class="page-item {{ $paginationInfo['currentPage'] <= 1 ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['currentPage'] - 1]) }}" 
                                           aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <!-- Page Numbers -->
                                    @php
                                        $currentPage = $paginationInfo['currentPage'];
                                        $totalPages = $paginationInfo['totalPages'];
                                        $start = max(1, $currentPage - 2);
                                        $end = min($totalPages, $currentPage + 2);
                                        
                                        // Ensure we show at least 5 pages if available
                                        if ($end - $start < 4) {
                                            if ($start == 1) {
                                                $end = min($totalPages, $start + 4);
                                            } else {
                                                $start = max(1, $end - 4);
                                            }
                                        }
                                    @endphp
                                    
                                    <!-- First page if not in range -->
                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif
                                    
                                    <!-- Page range -->
                                    @for($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                            @if($i == $currentPage)
                                                <span class="page-link">{{ $i }}</span>
                                            @else
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                            @endif
                                        </li>
                                    @endfor
                                    
                                    <!-- Last page if not in range -->
                                    @if($end < $totalPages)
                                        @if($end < $totalPages - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $totalPages]) }}">{{ $totalPages }}</a>
                                        </li>
                                    @endif
                                    
                                    <!-- Next Button -->
                                    <li class="page-item {{ $paginationInfo['currentPage'] >= $totalPages ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $paginationInfo['currentPage'] + 1]) }}" 
                                           aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            @endif
                        </div>
                        
                        <!-- Quick page navigation for large datasets -->
                        @if($paginationInfo['totalPages'] > 10)
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                Trang {{ $paginationInfo['currentPage'] }} / {{ $paginationInfo['totalPages'] }}
                                | 
                                <select class="form-select form-select-sm d-inline-block" style="width: auto;" 
                                        onchange="window.location.href='{{ request()->fullUrlWithQuery(['page' => '']) }}' + this.value">
                                    @for($i = 1; $i <= $paginationInfo['totalPages']; $i++)
                                        <option value="{{ $i }}" {{ $i == $paginationInfo['currentPage'] ? 'selected' : '' }}>
                                            Trang {{ $i }}
                                        </option>
                                    @endfor
                                </select>
                            </small>
                        </div>
                        @elseif($paginationInfo['totalPages'] > 1)
                        <div class="mt-2 text-center">
                            <small class="text-muted">
                                Trang {{ $paginationInfo['currentPage'] }} / {{ $paginationInfo['totalPages'] }}
                            </small>
                        </div>
                        @endif
                        
                        <!-- Items per page info -->
                        @if($paginationInfo['totalItems'] > 10)
                        <div class="mt-2 text-center">
                            <small class="text-muted">
                                {{ $paginationInfo['perPage'] }} danh mục mỗi trang
                            </small>
                        </div>
                        @endif
                    </div>
                    @elseif(!empty($paginatedCategories))
                    <!-- Fallback for old pagination format -->
                    @php
                        $perPage = 10;
                        $totalItems = count($categories ?? []);
                        $currentPage = request('page', 1);
                        $totalPages = ceil($totalItems / $perPage);
                        $startItem = ($currentPage - 1) * $perPage + 1;
                        $endItem = min($currentPage * $perPage, $totalItems);
                    @endphp
                    
                    <div class="border-top px-3 py-3">
                        <!-- Pagination Info -->
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted">
                                <small>
                                    Hiển thị {{ $startItem }}-{{ $endItem }} trong tổng số {{ $totalItems }} danh mục
                                    @if(request('search'))
                                        (đã lọc từ {{ count($categories) }} kết quả)
                                    @endif
                                </small>
                            </div>
                            
                            <!-- Pagination Controls -->
                            @if($totalPages > 1)
                            <nav aria-label="Category pagination">
                                <ul class="pagination pagination-sm mb-0">
                                    <!-- Previous Button -->
                                    <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage - 1]) }}" 
                                           aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                    
                                    <!-- Page Numbers -->
                                    @php
                                        $start = max(1, $currentPage - 2);
                                        $end = min($totalPages, $currentPage + 2);
                                        
                                        // Ensure we show at least 5 pages if available
                                        if ($end - $start < 4) {
                                            if ($start == 1) {
                                                $end = min($totalPages, $start + 4);
                                            } else {
                                                $start = max(1, $end - 4);
                                            }
                                        }
                                    @endphp
                                    
                                    <!-- First page if not in range -->
                                    @if($start > 1)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                                        </li>
                                        @if($start > 2)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                    @endif
                                    
                                    <!-- Page range -->
                                    @for($i = $start; $i <= $end; $i++)
                                        <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                            @if($i == $currentPage)
                                                <span class="page-link">{{ $i }}</span>
                                            @else
                                                <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                            @endif
                                        </li>
                                    @endfor
                                    
                                    <!-- Last page if not in range -->
                                    @if($end < $totalPages)
                                        @if($end < $totalPages - 1)
                                            <li class="page-item disabled">
                                                <span class="page-link">...</span>
                                            </li>
                                        @endif
                                        <li class="page-item">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $totalPages]) }}">{{ $totalPages }}</a>
                                        </li>
                                    @endif
                                    
                                    <!-- Next Button -->
                                    <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage + 1]) }}" 
                                           aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                            @endif
                        </div>
                        
                        <!-- Quick page navigation -->
                        @if($totalPages > 1)
                        <div class="mt-3 text-center">
                            <small class="text-muted">
                                Trang {{ $currentPage }} / {{ $totalPages }}
                                @if($totalPages > 10)
                                    | 
                                    <select class="form-select form-select-sm d-inline-block" style="width: auto;" 
                                            onchange="window.location.href='{{ request()->fullUrlWithQuery(['page' => '']) }}' + this.value">
                                        @for($i = 1; $i <= $totalPages; $i++)
                                            <option value="{{ $i }}" {{ $i == $currentPage ? 'selected' : '' }}>
                                                Trang {{ $i }}
                                            </option>
                                        @endfor
                                    </select>
                                @endif
                            </small>
                        </div>
                        @endif
                    </div>
                    @else
                    <div class="border-top px-3 py-3">
                        <div class="text-muted text-center">
                            <small>Không có danh mục nào để hiển thị</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

            <!-- Add Category Modal -->
            <div class="modal fade" id="addCategoryModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="POST" action="{{ route('dashboard.categories.store') }}">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Thêm danh mục</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên</label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name') }}" 
                                           required 
                                           maxlength="150">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô tả</label>
                                    <textarea class="form-control" 
                                              id="description" 
                                              name="description">{{ old('description') }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                <button type="submit" class="btn btn-primary">Lưu</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
