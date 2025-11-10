@extends('layouts.customer')

@section('title', 'Trang chủ - WebShop')

@section('content')
{{-- Hero Section --}}
<section class="hero-section">
    <div class="hero-decoration-1"></div>
    <div class="hero-decoration-2"></div>
    <div class="hero-decoration-3"></div>
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start mb-4 mb-md-0">
                <div class="hero-badge mb-3">
                    <i class="fas fa-star"></i> Nền tảng mua sắm uy tín
                </div>
                <h1 class="display-5 display-md-4 hero-title">
                    Chào mừng đến <span class="gradient-text">WebShop</span>
                </h1>
                <p class="lead hero-description">
                    <i class="fas fa-check-circle text-success me-2"></i>Khám phá hàng ngàn sản phẩm chất lượng với giá tốt nhất. 
                    <br><i class="fas fa-shipping-fast text-info me-2"></i>Mua sắm thật dễ dàng, giao hàng nhanh chóng!
                </p>
                <div class="hero-actions">
                    <a href="{{ route('products.index') }}" class="btn-hero btn-hero-primary">
                        <i class="fas fa-shopping-bag"></i> 
                        <span>Mua sắm ngay</span>
                    </a>
                    <a href="{{ route('products.promotions') }}" class="btn-hero btn-hero-secondary">
                        <i class="fas fa-fire"></i> 
                        <span>Khuyến mãi hot</span>
                    </a>
                </div>
                <div class="hero-stats mt-4">
                    <div class="hero-stat-item">
                        <div class="stat-number">1000+</div>
                        <div class="stat-label">Sản phẩm</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Khách hàng</div>
                    </div>
                    <div class="hero-stat-item">
                        <div class="stat-number">24/7</div>
                        <div class="stat-label">Hỗ trợ</div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 text-center d-none d-md-block">
                <div class="hero-image-wrapper">
                    <div class="hero-image-decoration"></div>
                    <img src="https://m.yodycdn.com/blog/hinh-nen-thien-nhien-4k-yody-vn-11.jpg" 
                         alt="Shopping" 
                         class="img-fluid hero-image">
                    <div class="hero-image-badge badge-1">
                        <i class="fas fa-shield-alt"></i> An toàn
                    </div>
                    <div class="hero-image-badge badge-2">
                        <i class="fas fa-truck"></i> Miễn phí ship
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Featured Categories --}}
<section class="container mb-5">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-th-large gradient-icon"></i> Danh mục nổi bật
        </h2>
        <p class="section-subtitle">Khám phá đa dạng các danh mục sản phẩm hấp dẫn</p>
    </div>
    <div class="row g-3 g-md-4">
        @if(isset($categories) && $categories->count() > 0)
            @foreach($categories->take(6) as $category)
                <div class="col-6 col-md-4 col-lg-2" data-aos="fade-up" data-aos-delay="{{ $loop->index * 100 }}">
                    @include('components.category-card', ['category' => $category])
                </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <div class="empty-state">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có danh mục nào</p>
                </div>
            </div>
        @endif
    </div>
</section>

{{-- Featured Products --}}
<section class="container mb-5">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-fire gradient-icon"></i> Sản phẩm nổi bật
        </h2>
        <p class="section-subtitle">Top sản phẩm được yêu thích nhất tháng này</p>
    </div>
    <div class="row g-3 g-md-4">
        @if(isset($featuredProducts) && $featuredProducts->count() > 0)
            @foreach($featuredProducts as $product)
                <div class="col-6 col-md-4 col-lg-3" data-aos="zoom-in" data-aos-delay="{{ $loop->index * 50 }}">
                    @include('components.product-card', ['product' => $product])
                </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <div class="empty-state">
                    <i class="fas fa-shopping-bag fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có sản phẩm nào</p>
                </div>
            </div>
        @endif
    </div>

    {{-- View All Button --}}
    @if(isset($featuredProducts) && $featuredProducts->count() > 0)
        <div class="text-center mt-5">
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-lg view-all-btn animated-border-btn">
                <span>Xem tất cả sản phẩm</span>
                <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    @endif
</section>

{{-- New Products --}}
<section class="container mb-5 new-products-section">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-sparkles gradient-icon"></i> Sản phẩm mới nhất
        </h2>
        <p class="section-subtitle">Cập nhật liên tục các mặt hàng mới nhất</p>
    </div>
    <div class="row g-3 g-md-4">
        @if(isset($newProducts) && $newProducts->count() > 0)
            @foreach($newProducts as $product)
                <div class="col-6 col-md-4 col-lg-3" data-aos="flip-left" data-aos-delay="{{ $loop->index * 50 }}">
                    @include('components.product-card', ['product' => $product, 'showBadge' => true])
                </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <div class="empty-state">
                    <i class="fas fa-star fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có sản phẩm mới</p>
                </div>
            </div>
        @endif
    </div>
</section>

{{-- Features Section --}}
<section class="container mb-5">
    <div class="section-header">
        <h2 class="section-title">
            <i class="fas fa-star gradient-icon"></i> Tại sao chọn chúng tôi?
        </h2>
        <p class="section-subtitle">Cam kết mang đến trải nghiệm mua sắm tuyệt vời</p>
    </div>
    <div class="row g-3 g-md-4">
        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="0">
            @include('components.feature-card', [
                'icon' => 'fas fa-shipping-fast',
                'title' => 'Giao hàng nhanh',
                'description' => 'Miễn phí vận chuyển cho đơn hàng trên 500k'
            ])
        </div>
        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="100">
            @include('components.feature-card', [
                'icon' => 'fas fa-shield-alt',
                'title' => 'Thanh toán an toàn',
                'description' => 'Hỗ trợ đa dạng phương thức thanh toán'
            ])
        </div>
        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="200">
            @include('components.feature-card', [
                'icon' => 'fas fa-undo-alt',
                'title' => 'Đổi trả dễ dàng',
                'description' => 'Chính sách đổi trả trong vòng 7 ngày'
            ])
        </div>
        <div class="col-6 col-md-3" data-aos="fade-up" data-aos-delay="300">
            @include('components.feature-card', [
                'icon' => 'fas fa-headset',
                'title' => 'Hỗ trợ 24/7',
                'description' => 'Đội ngũ CSKH luôn sẵn sàng hỗ trợ bạn'
            ])
        </div>
    </div>
</section>
@endsection

@section('scripts')
@endsection
