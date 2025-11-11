<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WebShop - Cửa hàng trực tuyến')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/customer.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive-utilities.css') }}">
    
    @yield('styles')
</head>
<body>
    <!-- Header Top -->
    <div class="header-top">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <i class="fas fa-phone"></i> Hotline: 1900-1234 | <i class="fas fa-envelope"></i> support@webshop.vn
                </div>
                <div class="col-md-6 text-end">
                    @auth
                        <div class="dropdown d-inline-block">
                            <a href="#" class="text-white text-decoration-none dropdown-toggle" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('profile.index') }}">
                                        <i class="fas fa-user-circle"></i> Quản lý tài khoản
                                    </a>
                                </li>
                                @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('manager'))
                                    <li>
                                        <a class="dropdown-item" href="{{ route('dashboard') }}">
                                            <i class="fas fa-tachometer-alt"></i> Dashboard
                                        </a>
                                    </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-white text-decoration-none me-3">
                            <i class="fas fa-sign-in-alt"></i> Đăng nhập
                        </a>
                        <a href="{{ route('register') }}" class="text-white text-decoration-none">
                            <i class="fas fa-user-plus"></i> Đăng ký
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <!-- Header Main -->
    <div class="header-main">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-6 col-md-3">
                    <a href="{{ route('home') }}" class="logo">
                        <i class="fas fa-shopping-bag"></i> <span class="d-none d-sm-inline">WebShop</span>
                    </a>
                </div>
                <div class="col-12 col-md-6 order-3 order-md-2 mt-3 mt-md-0">
                    <div class="search-bar">
                        <form action="{{ route('products.search') }}" method="GET">
                            <div class="input-group">
                                <input type="text" name="q" class="form-control" placeholder="Tìm kiếm sản phẩm..." value="{{ request('q') }}">
                                <button class="btn" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-6 col-md-3 order-2 order-md-3">
                    <div class="header-icons text-end">
                        @auth
                            <a href="{{ route('profile.index') }}" title="Tài khoản của tôi" class="d-none d-sm-inline">
                                <i class="fas fa-user-circle"></i>
                            </a>
                        @endauth
                        <a href="{{ route('cart.index') }}" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart"></i>
                            @if(isset($cartCount) && $cartCount > 0)
                                <span class="badge-cart">{{ $cartCount }}</span>
                            @endif
                        </a>
                        <a href="#" title="Yêu thích" class="d-none d-lg-inline">
                            <i class="fas fa-heart"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-main">
        <div class="container">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="fas fa-home"></i> Trang chủ
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-th-large"></i> Danh mục
                        </a>
                        <ul class="dropdown-menu">
                            @if(isset($categories))
                                @foreach($categories as $category)
                                    <li>
                                        <a class="dropdown-item" href="{{ route('category.show', $category->category_id) }}">
                                            {{ $category->name }}
                                        </a>
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.index') }}">
                            <i class="fas fa-box"></i> Sản phẩm
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('products.promotions') }}">
                            <i class="fas fa-fire"></i> Khuyến mãi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('about') }}">
                            <i class="fas fa-info-circle"></i> Về chúng tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('contact') }}">
                            <i class="fas fa-phone"></i> Liên hệ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-4">
                    <h5><i class="fas fa-shopping-bag"></i> WebShop</h5>
                    <p class="pe-0 pe-lg-3">Chúng tôi cung cấp các sản phẩm chất lượng cao với giá cả hợp lý. Mua sắm trực tuyến an toàn và tiện lợi.</p>
                    <div class="footer-social">
                        <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-6 col-md-6 col-lg-2">
                    <h5>Về chúng tôi</h5>
                    <ul>
                        <li><a href="{{ route('about') }}">Giới thiệu</a></li>
                        <li><a href="#">Tuyển dụng</a></li>
                        <li><a href="#">Tin tức</a></li>
                        <li><a href="{{ route('contact') }}">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-6 col-md-6 col-lg-3">
                    <h5>Hỗ trợ khách hàng</h5>
                    <ul>
                        <li><a href="#">Chính sách đổi trả</a></li>
                        <li><a href="#">Chính sách bảo mật</a></li>
                        <li><a href="#">Phương thức thanh toán</a></li>
                        <li><a href="#">Hướng dẫn đặt hàng</a></li>
                    </ul>
                </div>
                <div class="col-12 col-md-6 col-lg-3">
                    <h5>Thông tin liên hệ</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt me-2"></i> 123 Đường ABC, TP.HCM</li>
                        <li><i class="fas fa-phone me-2"></i> 1900-1234</li>
                        <li><i class="fas fa-envelope me-2"></i> support@webshop.vn</li>
                        <li><i class="fas fa-clock me-2"></i> 8:00 - 22:00 (Hàng ngày)</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="mb-0">&copy; {{ date('Y') }} WebShop. All rights reserved. Designed with <i class="fas fa-heart text-danger"></i></p>
            </div>
        </div>
    </footer>

    <!-- JavaScript Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="{{ asset('js/cart.js') }}"></script>
    <script src="{{ asset('js/enhancements.js') }}"></script>
    
    <script>
        // Initialize AOS (Animate On Scroll)
        AOS.init({
            duration: 800,
            once: true,
            offset: 100,
            easing: 'ease-out-cubic'
        });
    </script>
    
    @yield('scripts')
</body>
</html>
