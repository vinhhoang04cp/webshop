<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'WebShop - Cửa hàng trực tuyến')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        :root {
            --primary: #00d4aa;
            --secondary: #26d0ce;
            --dark: #008c73;
            --light: #f0fffe;
            --mint-text: #134e4a;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--mint-text);
            background: var(--light);
        }

        /* Header Styles */
        .header-top {
            background: var(--dark);
            color: white;
            padding: 10px 0;
            font-size: 0.9rem;
        }

        .header-main {
            background: white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 0;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-decoration: none;
        }

        .search-bar {
            max-width: 500px;
        }

        .search-bar .input-group {
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .search-bar input {
            border: none;
            padding: 12px 20px;
        }

        .search-bar button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 0 25px;
            color: white;
        }

        .header-icons a {
            color: var(--dark);
            font-size: 1.3rem;
            margin-left: 20px;
            position: relative;
            text-decoration: none;
            transition: color 0.3s;
        }

        .header-icons a:hover {
            color: var(--primary);
        }

        .badge-cart {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Navigation */
        .navbar-main {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            padding: 0;
        }

        .navbar-main .nav-link {
            color: white !important;
            padding: 15px 20px;
            transition: background 0.3s;
        }

        .navbar-main .nav-link:hover {
            background: rgba(255,255,255,0.1);
        }

        .navbar-main .dropdown-menu {
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        /* Main Content */
        .main-content {
            min-height: 60vh;
            padding: 30px 0;
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            padding: 80px 0;
            margin-bottom: 40px;
        }

        .hero-section h1 {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .hero-section p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }

        .btn-hero {
            background: white;
            color: var(--primary);
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.3s;
        }

        .btn-hero:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        /* Product Card */
        .product-card {
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background: white;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .product-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        .product-body {
            padding: 15px;
        }

        .product-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--dark);
            text-decoration: none;
        }

        .product-price {
            font-size: 1.3rem;
            color: var(--primary);
            font-weight: 700;
        }

        .btn-add-cart {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 20px;
            width: 100%;
            margin-top: 10px;
            font-weight: 600;
            transition: transform 0.3s;
        }

        .btn-add-cart:hover {
            transform: scale(1.05);
        }

        /* Footer */
        .footer {
            background: var(--dark);
            color: white;
            padding: 40px 0 20px;
            margin-top: 60px;
        }

        .footer h5 {
            font-weight: 700;
            margin-bottom: 20px;
            color: white;
        }

        .footer ul {
            list-style: none;
            padding: 0;
        }

        .footer ul li {
            margin-bottom: 10px;
        }

        .footer ul li a {
            color: #d1d5db;
            text-decoration: none;
            transition: color 0.3s;
        }

        .footer ul li a:hover {
            color: white;
        }

        .footer-social a {
            color: white;
            font-size: 1.5rem;
            margin-right: 15px;
            transition: color 0.3s;
        }

        .footer-social a:hover {
            color: var(--primary);
        }

        .footer-bottom {
            border-top: 1px solid rgba(255,255,255,0.1);
            margin-top: 30px;
            padding-top: 20px;
            text-align: center;
            color: #9ca3af;
        }

        /* Category Badge */
        .category-badge {
            background: var(--light);
            color: var(--dark);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
            display: inline-block;
        }

        /* Section Title */
        .section-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 30px;
            text-align: center;
            color: var(--dark);
        }

        .section-title::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            margin: 15px auto 0;
            border-radius: 2px;
        }
    </style>
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
                        <a href="{{ route('dashboard') }}" class="text-white text-decoration-none me-3">
                            <i class="fas fa-user"></i> {{ Auth::user()->name }}
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link text-white text-decoration-none p-0">
                                <i class="fas fa-sign-out-alt"></i> Đăng xuất
                            </button>
                        </form>
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
                <div class="col-md-3">
                    <a href="{{ route('home') }}" class="logo">
                        <i class="fas fa-shopping-bag"></i> WebShop
                    </a>
                </div>
                <div class="col-md-6">
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
                <div class="col-md-3">
                    <div class="header-icons text-end">
                        <a href="{{ route('cart.index') }}" title="Giỏ hàng">
                            <i class="fas fa-shopping-cart"></i>
                            @if(isset($cartCount) && $cartCount > 0)
                                <span class="badge-cart">{{ $cartCount }}</span>
                            @endif
                        </a>
                        <a href="#" title="Yêu thích">
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
                        <a class="nav-link" href="#">
                            <i class="fas fa-fire"></i> Khuyến mãi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
                            <i class="fas fa-info-circle"></i> Về chúng tôi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">
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
            <div class="row">
                <div class="col-md-4">
                    <h5><i class="fas fa-shopping-bag"></i> WebShop</h5>
                    <p>Chúng tôi cung cấp các sản phẩm chất lượng cao với giá cả hợp lý. Mua sắm trực tuyến an toàn và tiện lợi.</p>
                    <div class="footer-social">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div class="col-md-2">
                    <h5>Về chúng tôi</h5>
                    <ul>
                        <li><a href="#">Giới thiệu</a></li>
                        <li><a href="#">Tuyển dụng</a></li>
                        <li><a href="#">Tin tức</a></li>
                        <li><a href="#">Liên hệ</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Hỗ trợ khách hàng</h5>
                    <ul>
                        <li><a href="#">Chính sách đổi trả</a></li>
                        <li><a href="#">Chính sách bảo mật</a></li>
                        <li><a href="#">Phương thức thanh toán</a></li>
                        <li><a href="#">Hướng dẫn đặt hàng</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Thông tin liên hệ</h5>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, TP.HCM</li>
                        <li><i class="fas fa-phone"></i> 1900-1234</li>
                        <li><i class="fas fa-envelope"></i> support@webshop.vn</li>
                        <li><i class="fas fa-clock"></i> 8:00 - 22:00 (Hàng ngày)</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; {{ date('Y') }} WebShop. All rights reserved. Designed with <i class="fas fa-heart text-danger"></i></p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/cart.js') }}"></script>
    @yield('scripts')
</body>
</html>
