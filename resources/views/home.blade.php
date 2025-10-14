@extends('layouts.customer')

@section('title', 'Trang chủ - WebShop')

@section('content')
<!-- Hero Section -->
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
                <img src="https://via.placeholder.com/500x400/667eea/ffffff?text=Shopping+Online" alt="Shopping" class="img-fluid" style="border-radius: 20px;">
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="container mb-5">
    <h2 class="section-title">Danh mục nổi bật</h2>
    <div class="row g-4">
        @if(isset($categories) && $categories->count() > 0)
            @foreach($categories->take(6) as $category)
            <div class="col-md-4 col-lg-2">
                <a href="{{ route('category.show', $category->category_id) }}" class="text-decoration-none">
                    <div class="card text-center border-0 shadow-sm" style="border-radius: 12px; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body">
                            <i class="fas fa-box fa-3x mb-3" style="color: #667eea;"></i>
                            <h6 class="card-title mb-0">{{ $category->name }}</h6>
                            <small class="text-muted">{{ $category->products_count ?? 0 }} sản phẩm</small>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <p class="text-muted">Chưa có danh mục nào</p>
            </div>
        @endif
    </div>
</section>

<!-- Featured Products -->
<section class="container mb-5">
    <h2 class="section-title">Sản phẩm nổi bật</h2>
    <div class="row g-4">
        @if(isset($featuredProducts) && $featuredProducts->count() > 0)
            @foreach($featuredProducts as $product)
            <div class="col-md-6 col-lg-3">
                <div class="product-card">
                    <a href="{{ route('product.show', $product->product_id) }}">
                        <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x250/667eea/ffffff?text=Product' }}" 
                             alt="{{ $product->name }}" 
                             class="product-image">
                    </a>
                    <div class="product-body">
                        <span class="category-badge">{{ $product->category->name ?? 'Danh mục' }}</span>
                        <a href="{{ route('product.show', $product->product_id) }}" class="text-decoration-none">
                            <h5 class="product-title">{{ $product->name }}</h5>
                        </a>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="product-price">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                            <div class="text-warning">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="far fa-star"></i>
                            </div>
                        </div>
                        <button class="btn-add-cart" onclick="addToCart({{ $product->product_id }})">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <p class="text-muted">Chưa có sản phẩm nào</p>
            </div>
        @endif
    </div>
    
    @if(isset($featuredProducts) && $featuredProducts->count() > 0)
    <div class="text-center mt-4">
        <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-lg" style="border-radius: 25px;">
            Xem tất cả sản phẩm <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    @endif
</section>

<!-- New Products -->
<section class="container mb-5" style="background: #f9fafb; padding: 40px 20px; border-radius: 20px;">
    <h2 class="section-title">Sản phẩm mới nhất</h2>
    <div class="row g-4">
        @if(isset($newProducts) && $newProducts->count() > 0)
            @foreach($newProducts as $product)
            <div class="col-md-6 col-lg-3">
                <div class="product-card">
                    <div style="position: relative;">
                        <a href="{{ route('product.show', $product->product_id) }}">
                            <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x250/764ba2/ffffff?text=New+Product' }}" 
                                 alt="{{ $product->name }}" 
                                 class="product-image">
                        </a>
                        <span class="badge bg-danger" style="position: absolute; top: 10px; right: 10px;">Mới</span>
                    </div>
                    <div class="product-body">
                        <span class="category-badge">{{ $product->category->name ?? 'Danh mục' }}</span>
                        <a href="{{ route('product.show', $product->product_id) }}" class="text-decoration-none">
                            <h5 class="product-title">{{ $product->name }}</h5>
                        </a>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="product-price">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                            <small class="text-muted">{{ $product->created_at->diffForHumans() }}</small>
                        </div>
                        <button class="btn-add-cart" onclick="addToCart({{ $product->product_id }})">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        @else
            <div class="col-12 text-center">
                <p class="text-muted">Chưa có sản phẩm mới</p>
            </div>
        @endif
    </div>
</section>

<!-- Features Section -->
<section class="container mb-5">
    <div class="row g-4">
        <div class="col-md-3">
            <div class="text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-shipping-fast fa-3x" style="color: #667eea;"></i>
                </div>
                <h5>Giao hàng nhanh</h5>
                <p class="text-muted">Miễn phí vận chuyển cho đơn hàng trên 500k</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-shield-alt fa-3x" style="color: #667eea;"></i>
                </div>
                <h5>Thanh toán an toàn</h5>
                <p class="text-muted">Hỗ trợ đa dạng phương thức thanh toán</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-undo-alt fa-3x" style="color: #667eea;"></i>
                </div>
                <h5>Đổi trả dễ dàng</h5>
                <p class="text-muted">Chính sách đổi trả trong vòng 7 ngày</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-headset fa-3x" style="color: #667eea;"></i>
                </div>
                <h5>Hỗ trợ 24/7</h5>
                <p class="text-muted">Đội ngũ CSKH luôn sẵn sàng hỗ trợ bạn</p>
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
function addToCart(productId) {
    fetch(`/cart/add/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ quantity: 1 })
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Có lỗi xảy ra!');
            if(!data.success && data.message.includes('đăng nhập')) {
                window.location.href = '/login';
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
        window.location.href = '/login';
    });
}
</script>
@endsection
