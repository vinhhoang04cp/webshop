@extends('layouts.app')
@section('title', 'Quản lý tồn kho - WebShop Admin')
@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Quản lý tồn kho</h2>
                    <p class="text-muted mb-0">Theo dõi và quản lý tồn kho sản phẩm</p>
                </div>
            </div>
            @include('components.alerts')

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-boxes me-2"></i>Danh sách tồn kho</h5>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <form method="GET" action="{{ route('dashboard.inventory.index') }}" class="search-box flex-grow-1">
                            <input name="search" class="form-control form-control-sm" placeholder="Tìm kiếm sản phẩm..." value="{{ request('search') }}">
                            <input type="hidden" name="stock_status" value="{{ request('stock_status') }}">
                            <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                            <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                            <a href="{{ route('dashboard.inventory.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                        </form>
                        <div class="btn-group" role="group">
                            <a href="{{ route('dashboard.inventory.index', array_merge(request()->all(), ['stock_status' => ''])) }}" 
                               class="btn btn-sm {{ request('stock_status') === '' || !request('stock_status') ? 'btn-primary' : 'btn-outline-primary' }}">
                                Tất cả
                            </a>
                            <a href="{{ route('dashboard.inventory.index', array_merge(request()->all(), ['stock_status' => 'available'])) }}" 
                               class="btn btn-sm {{ request('stock_status') === 'available' ? 'btn-success' : 'btn-outline-success' }}">
                                Còn hàng
                            </a>
                            <a href="{{ route('dashboard.inventory.index', array_merge(request()->all(), ['stock_status' => 'low'])) }}" 
                               class="btn btn-sm {{ request('stock_status') === 'low' ? 'btn-warning' : 'btn-outline-warning' }}">
                                Sắp hết
                            </a>
                            <a href="{{ route('dashboard.inventory.index', array_merge(request()->all(), ['stock_status' => 'out'])) }}" 
                               class="btn btn-sm {{ request('stock_status') === 'out' ? 'btn-danger' : 'btn-outline-danger' }}">
                                Hết hàng
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Sản phẩm</th>
                                    <th>Danh mục</th>
                                    <th class="text-center">
                                        <a href="{{ route('dashboard.inventory.index', array_merge(request()->all(), ['sort_by' => 'stock_in', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark">
                                            Nhập kho
                                            @if(request('sort_by') === 'stock_in')
                                                <i class="fas fa-sort-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-center">
                                        <a href="{{ route('dashboard.inventory.index', array_merge(request()->all(), ['sort_by' => 'stock_out', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark">
                                            Xuất kho
                                            @if(request('sort_by') === 'stock_out')
                                                <i class="fas fa-sort-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-center">
                                        <a href="{{ route('dashboard.inventory.index', array_merge(request()->all(), ['sort_by' => 'current_stock', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark">
                                            Tồn kho
                                            @if(request('sort_by') === 'current_stock')
                                                <i class="fas fa-sort-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-center">Trạng thái</th>
                                    <th class="text-center">
                                        <a href="{{ route('dashboard.inventory.index', array_merge(request()->all(), ['sort_by' => 'updated_at', 'sort_order' => request('sort_order') === 'asc' ? 'desc' : 'asc'])) }}" 
                                           class="text-decoration-none text-dark">
                                            Cập nhật
                                            @if(request('sort_by') === 'updated_at' || !request('sort_by'))
                                                <i class="fas fa-sort-{{ request('sort_order') === 'asc' ? 'up' : 'down' }}"></i>
                                            @endif
                                        </a>
                                    </th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($error))
                                    <tr><td colspan="9" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>{{ $error }}</td></tr>
                                @elseif(empty($paginatedInventory))
                                    <tr><td colspan="9" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        @if(request('search'))
                                            Không tìm thấy "{{ request('search') }}"
                                        @else
                                            Chưa có dữ liệu tồn kho
                                        @endif
                                    </td></tr>
                                @else
                                    @foreach($paginatedInventory as $inventory)
                                        <tr>
                                            <td><strong>{{ $inventory->inventory_id }}</strong></td>
                                            <td>
                                                <strong>{{ $inventory->product->name ?? 'N/A' }}</strong>
                                            </td>
                                            <td>
                                                @if($inventory->product && $inventory->product->category)
                                                    <span class="badge bg-secondary">{{ $inventory->product->category->name }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="text-center"><span class="badge bg-info">{{ number_format($inventory->stock_in) }}</span></td>
                                            <td class="text-center"><span class="badge bg-warning">{{ number_format($inventory->stock_out) }}</span></td>
                                            <td class="text-center">
                                                <strong class="text-primary">{{ number_format($inventory->current_stock) }}</strong>
                                            </td>
                                            <td class="text-center">
                                                @if($inventory->current_stock == 0)
                                                    <span class="badge bg-danger">Hết hàng</span>
                                                @elseif($inventory->current_stock < 10)
                                                    <span class="badge bg-warning">Sắp hết</span>
                                                @else
                                                    <span class="badge bg-success">Còn hàng</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <small class="text-muted">{{ $inventory->updated_at ? $inventory->updated_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                            </td>
                                            <td class="text-center">
                                                <a href="{{ route('dashboard.inventory.show', $inventory->inventory_id) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
                                                <a href="{{ route('dashboard.inventory.edit', $inventory->inventory_id) }}" class="btn btn-sm btn-outline-secondary" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @if(isset($pagination) && $pagination && $pagination->hasPages())
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-muted small">
                                Hiển thị {{ $pagination->firstItem() }} - {{ $pagination->lastItem() }} trong tổng số {{ $pagination->total() }} bản ghi
                            </div>
                            <div>
                                {{ $pagination->links() }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
