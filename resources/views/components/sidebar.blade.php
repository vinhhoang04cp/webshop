{{-- Sidebar Dashboard --}}

<div class="col-md-3 col-lg-2 dashboard-sidebar d-flex flex-column">
    {{-- Header --}}
    <div class="sidebar-header">
        <h3><i class="fas fa-shield-alt"></i> WebShop</h3>
        <small class="text-muted" style="color: #9ca3af !important;">Admin Panel</small>
    </div>
    
    {{-- Menu Navigation --}}
    <nav class="nav flex-column sidebar-menu">
        {{-- Dashboard --}}
        <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        {{-- Sản phẩm --}}
        <a class="nav-link {{ request()->routeIs('dashboard.products.*') ? 'active' : '' }}" href="{{ route('dashboard.products.index') }}">
            <i class="fas fa-box"></i> Sản phẩm
        </a>
        
        {{-- Danh mục --}}
        <a class="nav-link {{ request()->routeIs('dashboard.categories.*') ? 'active' : '' }}" href="{{ route('dashboard.categories.index') }}">
            <i class="fas fa-tags"></i> Danh mục
        </a>
        
        {{-- Coupon (Manager & Admin only) --}}
        @if(auth()->user()->hasRole('manager') || auth()->user()->hasRole('admin'))
            <a class="nav-link {{ request()->routeIs('dashboard.coupons.*') ? 'active' : '' }}" href="{{ route('dashboard.coupons.index') }}">
                <i class="fas fa-ticket-alt"></i> Coupon
            </a>
        @endif
        
        {{-- Đơn hàng --}}
        <a class="nav-link {{ request()->routeIs('dashboard.orders.*') ? 'active' : '' }}" href="{{ route('dashboard.orders.index') }}">
            <i class="fas fa-shopping-cart"></i> Đơn hàng
        </a>
        
        {{-- Tồn kho --}}
        <a class="nav-link {{ request()->routeIs('dashboard.inventory.*') ? 'active' : '' }}" href="{{ route('dashboard.inventory.index') }}">
            <i class="fas fa-boxes"></i> Tồn kho
        </a>
        
        {{-- Báo cáo (Manager & Admin only) --}}
        @if(auth()->user()->hasRole('manager') || auth()->user()->hasRole('admin'))
            <a class="nav-link {{ request()->routeIs('dashboard.reports.*') ? 'active' : '' }}" href="{{ route('dashboard.reports.index') }}">
                <i class="fas fa-chart-bar"></i> Báo cáo
            </a>
        @endif
        
        {{-- Người dùng (Admin only) --}}
        @if(auth()->user()->isAdmin())
            <a class="nav-link {{ request()->routeIs('dashboard.users.*') ? 'active' : '' }}" href="{{ route('dashboard.users.index') }}">
                <i class="fas fa-users"></i> Người dùng
            </a>
        @endif
        
        {{-- Divider --}}
        <div class="border-top mt-3 pt-3" style="border-color: rgba(255,255,255,0.1) !important;"></div>
        
        {{-- Tài khoản của tôi --}}
        <a class="nav-link {{ request()->routeIs('profile.*') ? 'active' : '' }}" href="{{ route('profile.index') }}">
            <i class="fas fa-user-circle"></i> Tài khoản của tôi
        </a>
    </nav>
    
    {{-- User Info --}}
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
