@extends('layouts.app')

@section('title', 'Sản phẩm - WebShop Admin')

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
                <a class="nav-link active" href="{{ route('dashboard.products.index') }}">
                    <i class="fas fa-box"></i> Sản phẩm
                </a>
                <a class="nav-link" href="{{ route('dashboard.categories.index') }}">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <a class="nav-link" href="{{ route('dashboard.orders.index') }}">
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
                    <h2>Quản lý sản phẩm</h2>
                    <p class="text-muted mb-0">Quản lý sản phẩm trong cửa hàng</p>
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
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách sản phẩm</h5>
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm sản phẩm
                            </a>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('dashboard.products.index') }}" class="search-box">
                                <input name="search" class="form-control form-control-sm" 
                                       placeholder="Tìm kiếm theo tên hoặc mô tả..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                                <a href="{{ route('dashboard.products.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i> Xóa
                                </a>
                            </form>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">
                                @if(isset($paginatedProducts) && count($paginatedProducts) > 0)
                                    Hiển thị {{ count($paginatedProducts) }} / {{ count($products ?? []) }} sản phẩm
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 80px;">ID</th>
                                    <th style="width: 100px;">Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th style="width: 120px;">Giá</th>
                                    <th style="width: 150px;">Danh mục</th>
                                    <th style="width: 250px;" class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($error))
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-danger">
                                            <i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>
                                            {{ $error }}
                                        </td>
                                    </tr>
                                @elseif(empty($paginatedProducts))
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                            @if(request('search'))
                                                Không tìm thấy sản phẩm nào với từ khóa "{{ request('search') }}"
                                            @else
                                                Chưa có sản phẩm nào
                                                <br><br>
                                                <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary">
                                                    <i class="fas fa-plus me-2"></i>Thêm sản phẩm đầu tiên
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @else
                                    @foreach($paginatedProducts as $product)
                                        <tr>
                                            <td><strong>{{ $product->product_id }}</strong></td>
                                            <td>
                                                @if($product->image_url)
                                                    <img src="{{ $product->image_url }}" 
                                                         alt="{{ $product->name }}" 
                                                         class="rounded" 
                                                         style="width: 60px; height: 60px; object-fit: cover;"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="d-flex align-items-center justify-content-center bg-light rounded text-muted" 
                                                         style="width: 60px; height: 60px; display: none;">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                @else
                                                    <div class="d-flex align-items-center justify-content-center bg-light rounded text-muted" 
                                                         style="width: 60px; height: 60px;">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <strong>{{ $product->name }}</strong>
                                                    @if($product->description)
                                                        <br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-primary fw-bold">
                                                    {{ number_format($product->price, 0, ',', '.') }} VNĐ
                                                </span>
                                            </td>
                                            <td>
                                                @if($product->category)
                                                    <span class="badge bg-secondary">{{ $product->category->name }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('dashboard.products.show', $product->product_id) }}" 
                                                       class="btn btn-sm btn-outline-info"
                                                       title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('dashboard.products.edit', $product->product_id) }}" 
                                                       class="btn btn-sm btn-outline-secondary"
                                                       title="Chỉnh sửa">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteProductModal{{ $product->product_id }}"
                                                            title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Delete Modal for each product -->
                                        <div class="modal fade" id="deleteProductModal{{ $product->product_id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('dashboard.products.destroy', $product->product_id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                                                Xóa sản phẩm
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-warning">
                                                                <i class="fas fa-warning me-2"></i>
                                                                <strong>Cảnh báo:</strong> Hành động này không thể hoàn tác!
                                                            </div>
                                                            
                                                            <p>Bạn có chắc chắn muốn xóa sản phẩm <strong>"{{ $product->name }}"</strong>?</p>
                                                            
                                                            <div class="bg-light p-3 rounded">
                                                                <div class="row">
                                                                    <div class="col-3">
                                                                        @if($product->image_url)
                                                                            <img src="{{ $product->image_url }}" 
                                                                                 alt="{{ $product->name }}" 
                                                                                 class="img-fluid rounded">
                                                                        @else
                                                                            <div class="bg-secondary rounded d-flex align-items-center justify-content-center text-white" 
                                                                                 style="height: 60px;">
                                                                                <i class="fas fa-image"></i>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                    <div class="col-9">
                                                                        <small class="text-muted">
                                                                            <strong>ID:</strong> #{{ $product->product_id }}<br>
                                                                            <strong>Tên:</strong> {{ $product->name }}<br>
                                                                            <strong>Giá:</strong> {{ number_format($product->price, 0, ',', '.') }} VNĐ<br>
                                                                            <strong>Danh mục:</strong> {{ $product->category->name ?? 'N/A' }}
                                                                        </small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="fas fa-times me-2"></i>Hủy
                                                            </button>
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fas fa-trash me-2"></i>Xóa sản phẩm
                                                            </button>
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
                    
                    <!-- Pagination -->
                    @if(!empty($paginatedProducts))
                    @php
                        $perPage = 12;
                        $totalItems = count($products ?? []);
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
                                    Hiển thị {{ $startItem }}-{{ $endItem }} trong tổng số {{ $totalItems }} sản phẩm
                                    @if(request('search'))
                                        (tìm kiếm: "{{ request('search') }}")
                                    @endif
                                </small>
                            </div>
                            
                            <!-- Pagination Controls -->
                            @if($totalPages > 1)
                            <nav aria-label="Product pagination">
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
                            <small>Không có sản phẩm nào để hiển thị</small>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
