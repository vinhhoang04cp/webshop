@extends('layouts.app')

@section('title', 'Quản lý người dùng - WebShop Admin')

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
                <a class="nav-link" href="{{ route('dashboard.categories.index') }}">
                    <i class="fas fa-tags"></i> Danh mục
                </a>
                <a class="nav-link" href="{{ route('dashboard.orders.index') }}">
                    <i class="fas fa-shopping-cart"></i> Đơn hàng
                </a>
                @if(Auth::user()->isAdmin())
                <a class="nav-link active" href="{{ route('dashboard.users.index') }}">
                    <i class="fas fa-users"></i> Người dùng
                </a>
                @endif
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
                    <h2>Quản lý người dùng</h2>
                    <p class="text-muted mb-0">Quản lý người dùng và phân quyền</p>
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
                            <h5 class="mb-0"><i class="fas fa-list me-2"></i>Danh sách người dùng</h5>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <form method="GET" action="{{ route('dashboard.users.index') }}" class="search-box">
                                <input name="search" class="form-control form-control-sm" 
                                       placeholder="Tìm kiếm theo tên, email..." 
                                       value="{{ request('search') }}">
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                                <a href="{{ route('dashboard.users.index') }}" class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-times"></i> Xóa
                                </a>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">ID</th>
                                    <th>Tên</th>
                                    <th>Email</th>
                                    <th>Số điện thoại</th>
                                    <th>Roles</th>
                                    <th>Ngày tạo</th>
                                    <th class="text-center">Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td class="ps-4">#{{ $user->id }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle me-2">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <strong>{{ $user->name }}</strong>
                                        </div>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->phone ?? 'Chưa có' }}</td>
                                    <td>
                                        @if($user->roles->count() > 0)
                                            @foreach($user->roles as $role)
                                                <span class="badge bg-primary me-1">{{ $role->role_display_name }}</span>
                                            @endforeach
                                        @else
                                            <span class="badge bg-secondary">Chưa có role</span>
                                        @endif
                                    </td>
                                    <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('dashboard.users.show', $user->id) }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if(Auth::user()->isAdmin())
                                            <a href="{{ route('dashboard.users.edit', $user->id) }}" 
                                               class="btn btn-sm btn-outline-warning" 
                                               title="Sửa quyền">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($user->id != Auth::user()->id)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Xóa người dùng"
                                                    onclick="confirmDelete({{ $user->id }}, '{{ $user->name }}')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                            @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                        Không tìm thấy người dùng nào
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden delete forms -->
                    @foreach($users as $user)
                        @if($user->id != Auth::user()->id)
                        <form id="delete-form-{{ $user->id }}" 
                              action="{{ route('dashboard.users.destroy', $user->id) }}" 
                              method="POST" 
                              class="d-none">
                            @csrf
                            @method('DELETE')
                        </form>
                        @endif
                    @endforeach
                </div>
                @if($users->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            Hiển thị {{ $users->firstItem() }} - {{ $users->lastItem() }} trong tổng số {{ $users->total() }} người dùng
                        </div>
                        {{ $users->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.avatar-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 16px;
}
</style>

<script>
function confirmDelete(userId, userName) {
    if (confirm(`Bạn có chắc chắn muốn xóa người dùng "${userName}"?\n\nHành động này không thể hoàn tác!`)) {
        document.getElementById('delete-form-' + userId).submit();
    }
}
</script>
@endsection