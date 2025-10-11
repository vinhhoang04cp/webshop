@extends('layouts.app')
@section('title', 'Sản phẩm - WebShop Admin')
@section('content')
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar')
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Quản lý sản phẩm</h2>
                    <p class="text-muted mb-0">Quản lý sản phẩm trong cửa hàng</p>
                </div>
            </div>
            @include('components.alerts')

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách sản phẩm</h5>
                        <a href="{{ route('dashboard.products.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Thêm sản phẩm</a>
                    </div>
                    <div class="d-flex gap-2">
                        <form method="GET" action="{{ route('dashboard.products.index') }}" class="search-box flex-grow-1">
                            <input name="search" class="form-control form-control-sm" placeholder="Tìm kiếm sản phẩm..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                            <a href="{{ route('dashboard.products.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Hình ảnh</th>
                                    <th>Tên sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Danh mục</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($error))
                                    <tr><td colspan="6" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>{{ $error }}</td></tr>
                                @elseif(empty($paginatedProducts))
                                    <tr><td colspan="6" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        @if(request('search'))Không tìm thấy "{{ request('search') }}"
                                        @else Chưa có sản phẩm nào<br><a href="{{ route('dashboard.products.create') }}" class="btn btn-primary mt-2"><i class="fas fa-plus me-2"></i>Thêm sản phẩm</a>
                                        @endif
                                    </td></tr>
                                @else
                                    @foreach($paginatedProducts as $product)
                                        <tr>
                                            <td><strong>{{ $product->product_id }}</strong></td>
                                            <td>
                                                @if($product->image_url)
                                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="rounded" style="width:60px;height:60px;object-fit:cover" onerror="this.style.display='none'">
                                                @else
                                                    <div class="bg-light rounded text-muted d-flex align-items-center justify-content-center" style="width:60px;height:60px"><i class="fas fa-image"></i></div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $product->name }}</strong>
                                                @if($product->description)<br><small class="text-muted">{{ Str::limit($product->description, 50) }}</small>@endif
                                            </td>
                                            <td><span class="text-primary fw-bold">{{ number_format($product->price) }} VNĐ</span></td>
                                            <td>@if($product->category)<span class="badge bg-secondary">{{ $product->category->name }}</span>@else-@endif</td>
                                            <td class="text-center">
                                                <a href="{{ route('dashboard.products.show', $product->product_id) }}" class="btn btn-sm btn-outline-info" title="Xem"><i class="fas fa-eye"></i></a>
                                                <a href="{{ route('dashboard.products.edit', $product->product_id) }}" class="btn btn-sm btn-outline-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $product->product_id }}" title="Xóa"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="deleteModal{{ $product->product_id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('dashboard.products.destroy', $product->product_id) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Xóa sản phẩm</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="alert alert-warning"><i class="fas fa-warning me-2"></i><strong>Cảnh báo:</strong> Hành động này không thể hoàn tác!</div>
                                                            <p>Xóa sản phẩm <strong>"{{ $product->name }}"</strong>?</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times me-2"></i>Hủy</button>
                                                            <button type="submit" class="btn btn-danger"><i class="fas fa-trash me-2"></i>Xóa</button>
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
                    @if(!empty($paginatedProducts))
                    @php
                        $perPage = 12;
                        $totalItems = count($products ?? []);
                        $currentPage = request('page', 1);
                        $totalPages = ceil($totalItems / $perPage);
                    @endphp
                    <div class="border-top p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Hiển thị {{ ($currentPage-1)*$perPage+1 }}-{{ min($currentPage*$perPage, $totalItems) }} / {{ $totalItems }}</small>
                            @if($totalPages > 1)
                            <nav>
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item {{ $currentPage <= 1 ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage-1]) }}">&laquo;</a>
                                    </li>
                                    @for($i = max(1,$currentPage-2); $i <= min($totalPages,$currentPage+2); $i++)
                                        <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                            <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                        </li>
                                    @endfor
                                    <li class="page-item {{ $currentPage >= $totalPages ? 'disabled' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $currentPage+1]) }}">&raquo;</a>
                                    </li>
                                </ul>
                            </nav>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
