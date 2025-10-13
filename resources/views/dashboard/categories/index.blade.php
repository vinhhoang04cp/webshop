@extends('layouts.app') {{-- Ke ế thừa từ layout chính --}}
@section('title', 'Danh mục - WebShop Admin') {{-- Tiêu đề trang --}}
@section('content') {{-- Nội dung chính --}}
<div class="container-fluid p-0">
    <div class="row g-0">
        @include('components.sidebar') {{-- Thanh điều hướng bên --}}
        <div class="col-md-9 col-lg-10 dashboard-content">
            <div class="dashboard-header">
                <div>
                    <h2>Quản lý danh mục</h2>
                    <p class="text-muted mb-0">Quản lý danh mục sản phẩm</p>
                </div>
            </div>
            @include('components.alerts') {{-- Hiển thị thông báo --}}
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách danh mục</h5>
                        <a href="{{ route('dashboard.categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Thêm danh mục</a>
                    </div>
                    <form method="GET" action="{{ route('dashboard.categories.index') }}" class="search-box">
                        <input name="search" class="form-control form-control-sm" placeholder="Tìm kiếm theo tên..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-sm btn-outline-primary"><i class="fas fa-search"></i></button>
                        <a href="{{ route('dashboard.categories.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-times"></i></a>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên danh mục</th>
                                    <th>Mô tả</th>
                                    <th class="text-center">Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($error)) {{-- Neu co loi xay ra --}}
                                    <tr><td colspan="4" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle fa-2x mb-2 d-block"></i>{{ $error }}</td></tr> {{-- Hien thi loi --}}
                                @elseif($categories->isEmpty())
                                    <tr><td colspan="4" class="text-center py-4 text-muted"><i class="fas fa-inbox fa-2x mb-2 d-block"></i>Không có danh mục nào</td></tr>
                                @else
                                    @foreach($categories as $category) {{-- Vong lap qua moi danh muc, category --}}
                                        <tr>
                                            <td><strong>{{ $category->category_id }}</strong></td> {{-- $category->id query tu csdl --}}
                                            <td>{{ $category->name }}</td> {{-- $category->name query tu csdl --}}
                                            <td class="text-muted">{{ $category->description ?: '-' }}</td> {{-- $category->description query tu csdl --}}
                                            <td class="text-center">
                                                <a href="{{ route('dashboard.categories.show', $category->category_id) }}" class="btn btn-sm btn-outline-info" title="Xem"><i class="fas fa-eye"></i></a>
                                                <a href="{{ route('dashboard.categories.edit', $category->category_id) }}" class="btn btn-sm btn-outline-secondary" title="Sửa"><i class="fas fa-edit"></i></a>
                                                <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal{{ $category->category_id }}" title="Xóa"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        <div class="modal fade" id="deleteModal{{ $category->category_id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form method="POST" action="{{ route('dashboard.categories.destroy', $category->category_id) }}">
                                                        @csrf {{-- Bao ve CSRF --}}
                                                        @method('DELETE') 
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Xóa danh mục</h5> {{-- Tieu de modal --}}
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>Xóa danh mục <strong>{{ $category->name }}</strong>?</p> {{-- Hien thi ten danh muc can xoa --}}
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
                    @if($categories->hasPages())
                    <div class="border-top p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">Hiển thị {{ $categories->firstItem() }}-{{ $categories->lastItem() }} / {{ $categories->total() }}</small>
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
