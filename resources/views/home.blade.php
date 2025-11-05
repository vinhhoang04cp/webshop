@extends('layouts.customer')

@section('title', 'Trang chủ - WebShop')

@section('content')
{{-- Hero Section --}}
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h1>Chào mừng đến WebShop</h1>
                <p>Khám phá hàng ngàn sản phẩm chất lượng với giá tốt nhất. Mua sắm thật dễ dàng, giao hàng nhanh chóng!</p>
                <a href="{{ route('products.index') }}" class="btn-hero">
                    <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                </a>
            </div>
            <div class="col-md-6 text-center">
                <img src="https://m.yodycdn.com/blog/hinh-nen-thien-nhien-4k-yody-vn-11.jpg" 
                     alt="Shopping" 
                     class="img-fluid" 
                     style="border-radius: 20px;">
            </div>
        </div>
    </div>
</section>

{{-- Featured Categories --}}
<section class="container mb-5">
    <h2 class="section-title">Danh mục nổi bật</h2>
    <div class="row g-4">
        @if(isset($categories) && $categories->count() > 0)
            @foreach($categories->take(6) as $category)
                <div class="col-md-4 col-lg-2">
                    @include('components.category-card', ['category' => $category])
                </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <p class="text-muted">Chưa có danh mục nào</p>
            </div>
        @endif
    </div>
</section>

{{-- Featured Products --}}
<section class="container mb-5">
    <h2 class="section-title">Sản phẩm nổi bật</h2>
    <div class="row g-4">
        @if(isset($featuredProducts) && $featuredProducts->count() > 0)
            @foreach($featuredProducts as $product)
                <div class="col-md-6 col-lg-3">
                    @include('components.product-card', ['product' => $product])
                </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <p class="text-muted">Chưa có sản phẩm nào</p>
            </div>
        @endif
    </div>

    {{-- View All Button --}}
    @if(isset($featuredProducts) && $featuredProducts->count() > 0)
        <div class="text-center mt-4">
            <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-lg" style="border-radius: 25px;">
                Xem tất cả sản phẩm <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    @endif
</section>

{{-- New Products --}}
<section class="container mb-5" style="background: #f9fafb; padding: 40px 20px; border-radius: 20px;">
    <h2 class="section-title">Sản phẩm mới nhất</h2>
    <div class="row g-4">
        @if(isset($newProducts) && $newProducts->count() > 0)
            @foreach($newProducts as $product)
                <div class="col-md-6 col-lg-3">
                    @include('components.product-card', ['product' => $product, 'showBadge' => true])
                </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <p class="text-muted">Chưa có sản phẩm mới</p>
            </div>
        @endif
    </div>
</section>

{{-- Features Section --}}
<section class="container mb-5">
    <div class="row g-4">
        <div class="col-md-3">
            @include('components.feature-card', [
                'icon' => 'fas fa-shipping-fast',
                'title' => 'Giao hàng nhanh',
                'description' => 'Miễn phí vận chuyển cho đơn hàng trên 500k'
            ])
        </div>
        <div class="col-md-3">
            @include('components.feature-card', [
                'icon' => 'fas fa-shield-alt',
                'title' => 'Thanh toán an toàn',
                'description' => 'Hỗ trợ đa dạng phương thức thanh toán'
            ])
        </div>
        <div class="col-md-3">
            @include('components.feature-card', [
                'icon' => 'fas fa-undo-alt',
                'title' => 'Đổi trả dễ dàng',
                'description' => 'Chính sách đổi trả trong vòng 7 ngày'
            ])
        </div>
        <div class="col-md-3">
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
