@extends('layouts.app')

@section('title', 'Danh mục - WebShop Admin')

@section('content')
<div class="container-fluid p-0">
    <div class="row">
        <div class="col-12">
            <h2 class="mb-3">Danh mục</h2>

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Danh sách danh mục</h5>
                        <div class="mt-2">
                            <form method="GET" action="{{ route('dashboard.categories.index') }}" class="d-flex">
                                <input name="search" class="form-control form-control-sm me-2" 
                                       placeholder="Tìm kiếm theo tên..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary me-2">Tìm</button>
                                <a href="{{ route('dashboard.categories.index') }}" class="btn btn-sm btn-outline-secondary">Xóa</a>
                            </form>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                        Thêm danh mục
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Tên</th>
                                    <th>Mô tả</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->category_id }}</td>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->description }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editCategoryModal{{ $category->category_id }}">
                                                Sửa
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#deleteCategoryModal{{ $category->category_id }}">
                                                Xóa
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal for each category -->
                                    <div class="modal fade" id="editCategoryModal{{ $category->category_id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form method="POST" action="{{ route('dashboard.categories.update', $category->category_id) }}">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Sửa danh mục</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="editName{{ $category->category_id }}" class="form-label">Tên</label>
                                                            <input type="text" 
                                                                   class="form-control @error('name') is-invalid @enderror" 
                                                                   id="editName{{ $category->category_id }}" 
                                                                   name="name" 
                                                                   value="{{ old('name', $category->name) }}" 
                                                                   required 
                                                                   maxlength="150">
                                                            @error('name')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="editDescription{{ $category->category_id }}" class="form-label">Mô tả</label>
                                                            <textarea class="form-control" 
                                                                      id="editDescription{{ $category->category_id }}" 
                                                                      name="description">{{ old('description', $category->description) }}</textarea>
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
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Không có danh mục nào</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $categories->links() }}
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
