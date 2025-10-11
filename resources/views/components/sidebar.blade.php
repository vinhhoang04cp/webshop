<div class="col-md-3 col-lg-2 dashboard-sidebar d-flex flex-column">
    <div class="sidebar-header">
        <h3><i class="fas fa-shield-alt"></i> WebShop</h3>
        <small class="text-muted" style="color: #9ca3af !important;">Admin Panel</small>
    </div>
    <nav class="nav flex-column sidebar-menu">
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        <a class="nav-link {{ request()->routeIs('dashboard.products.*') ? 'active' : '' }}" href="{{ route('dashboard.products.index') }}">
            <i class="fas fa-box"></i> Sản phẩm
        </a>
        <a class="nav-link {{ request()->routeIs('dashboard.categories.*') ? 'active' : '' }}" href="{{ route('dashboard.categories.index') }}">
            <i class="fas fa-tags"></i> Danh mục
        </a>
        <a class="nav-link {{ request()->routeIs('dashboard.orders.*') ? 'active' : '' }}" href="{{ route('dashboard.orders.index') }}">
            <i class="fas fa-shopping-cart"></i> Đơn hàng
        </a>
        @if(auth()->user()->isAdmin())
        <a class="nav-link {{ request()->routeIs('dashboard.users.*') ? 'active' : '' }}" href="{{ route('dashboard.users.index') }}">
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
