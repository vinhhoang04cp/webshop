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
                <a href="{{ route('products.index') }}" class="btn-hero"> {{-- Link Route den trang danh sach san pham --}}
                    <i class="fas fa-shopping-bag"></i> Mua sắm ngay
                </a>
            </div>
            <div class="col-md-6 text-center">
                <img src="https://m.yodycdn.com/blog/hinh-nen-thien-nhien-4k-yody-vn-11.jpg" alt="Shopping" class="img-fluid" style="border-radius: 20px;">
            </div>
        </div>
    </div>
</section>

<!-- Featured Categories -->
<section class="container mb-5">
    <h2 class="section-title">Danh mục nổi bật</h2>
    <div class="row g-4">
        @if(isset($categories) && $categories->count() > 0) {{-- Neu co ton tai danh muc va so luong danh muc lon hon 6 --}}
            @foreach($categories->take(6) as $category) {{-- Bat dau vong lap danh muc va chi lay toi da 6 danh muc --}}
            <div class="col-md-4 col-lg-2">
                <a href="{{ route('category.show', $category->category_id) }}" class="text-decoration-none">
                    {{-- route('category.show', $category->category_id) la link den trang danh muc san pham voi category_id tuong ung --}}
                    <div class="card text-center border-0 shadow-sm" style="border-radius: 12px; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                        <div class="card-body">
                            <i class="fas fa-box fa-3x mb-3" style="color: #667eea;"></i> {{-- Icon danh muc --}}
                            <h6 class="card-title mb-0">{{ $category->name }}</h6> {{-- Hien thi ten danh muc --}}
                            <small class="text-muted">{{ $category->products_count ?? 0 }} sản phẩm</small> {{-- Hien thi so luong san pham trong danh muc, neu khong co thi hien thi 0 --}}
                        </div>
                    </div>
                </a>
            </div>
            @endforeach {{-- Ket thuc vong lap danh muc --}}
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
        @if(isset($featuredProducts) && $featuredProducts->count() > 0) {{-- Neu co ton tai san pham noi bat --}}
            @foreach($featuredProducts as $product) {{-- Bat dau vong lap san pham noi bat --}}
            <div class="col-md-6 col-lg-3"> {{-- Hien thi 4 san pham tren 1 hang tren man hinh lon --}}
                {{-- Hien thi the hien thong tin san pham --}}
                <div class="product-card">
                    <a href="{{ route('product.show', $product->product_id) }}"> {{-- Link den trang chi tiet san pham voi product_id tuong ung --}}
                        <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x250/667eea/ffffff?text=Product' }}"  
                        {{-- Hien thi anh san pham, neu khong co thi hien thi anh mac dinh --}}
                             alt="{{ $product->name }}" {{-- Hien thi ten san pham --}}
                             class="product-image"> {{-- Class CSS de dinh dang anh san pham --}}
                    </a>
                    {{-- Hien thi thong tin san pham --}}
                    <div class="product-body">
                        <span class="category-badge">{{ $product->category->name ?? 'Danh mục' }}</span>
                        <a href="{{ route('product.show', $product->product_id) }}" class="text-decoration-none">
                            <h5 class="product-title">{{ $product->name }}</h5>
                        </a>
                        <div class="mb-2">
                            @if($product->original_price)
                                <div class="text-muted small text-decoration-line-through">
                                    {{ number_format($product->original_price, 0, ',', '.') }}₫
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="product-price text-danger">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                    <span class="badge bg-danger" style="font-size: 0.7rem;">
                                        -{{ number_format((($product->original_price - $product->price) / $product->original_price) * 100, 0) }}%
                                    </span>
                                </div>
                            @else
                                <span class="product-price">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                            @endif
                        </div>
                        <div class="text-warning">
                            @php
                                $avgRating = $product->averageRating();
                                $fullStars = floor($avgRating);
                            @endphp
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $fullStars)
                                    <i class="fas fa-star"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <button class="btn-add-cart" onclick="addToCart({{ $product->product_id }})"> 
                            {{-- onclick goi ham addToCart voi product_id tuong ung --}}
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ {{-- Hien thi icon gio hang va chu "Them vao gio" --}}
                        </button>
                    </div>
                </div>
            </div>
            @endforeach {{-- Ket thuc vong lap san pham noi bat --}}
        @else {{-- Neu khong co san pham noi bat --}}
            <div class="col-12 text-center">
                <p class="text-muted">Chưa có sản phẩm nào</p>
            </div>
        @endif
    </div>

    @if(isset($featuredProducts) && $featuredProducts->count() > 0) {{-- Neu co ton tai san pham noi bat va co san pham lon hon 0 --}}
    <div class="text-center mt-4">
        <a href="{{ route('products.index') }}" class="btn btn-outline-primary btn-lg" style="border-radius: 25px;">
            {{-- Link den trang danh sach san pham --}}
            Xem tất cả sản phẩm <i class="fas fa-arrow-right"></i>
        </a>
    </div>
    @endif
</section>

<!-- New Products -->
<section class="container mb-5" style="background: #f9fafb; padding: 40px 20px; border-radius: 20px;">
    <h2 class="section-title">Sản phẩm mới nhất</h2>
    <div class="row g-4">
        @if(isset($newProducts) && $newProducts->count() > 0) {{-- Neu co ton tai san pham moi --}}
            @foreach($newProducts as $product) {{-- Bat dau vong lap san pham moi --}}
            <div class="col-md-6 col-lg-3"> 
                <div class="product-card">
                    <div style="position: relative;"> {{-- De hien thi badge "Moi" o goc phai tren anh san pham --}}
                        <a href="{{ route('product.show', $product->product_id) }}">
                            <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x250/764ba2/ffffff?text=New+Product' }}"
                            {{-- link den anh san pham moi, neu khong co thi hien thi anh mac dinh --}} 
                                 alt="{{ $product->name }}" {{-- Hien thi ten san pham moi --}}
                                 class="product-image"> {{-- Class CSS de dinh dang anh san pham moi --}}
                        </a>
                        <span class="badge bg-danger" style="position: absolute; top: 10px; right: 10px;">Mới</span>
                    </div>
                    <div class="product-body">
                        <span class="category-badge">{{ $product->category->name ?? 'Danh mục' }}</span> {{-- Hien thi ten danh muc san pham, neu khong co thi hien thi "Danh muc" --}} 
                        <a href="{{ route('product.show', $product->product_id) }}" class="text-decoration-none"> {{-- Link den trang chi tiet san pham moi voi product_id tuong ung --}}
                            <h5 class="product-title">{{ $product->name }}</h5> {{-- Hien thi ten san pham moi khi goi den $product->name --}}
                        </a>
                        <div class="mb-2">
                            @if($product->original_price)
                                <div class="text-muted small text-decoration-line-through">
                                    {{ number_format($product->original_price, 0, ',', '.') }}₫
                                </div>
                                <span class="product-price text-danger">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                                <span class="badge bg-danger ms-1" style="font-size: 0.7rem;">
                                    -{{ number_format((($product->original_price - $product->price) / $product->original_price) * 100, 0) }}%
                                </span>
                            @else
                                <span class="product-price">{{ number_format($product->price, 0, ',', '.') }}₫</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $product->created_at->diffForHumans() }}</small>
                        <button class="btn-add-cart" onclick="addToCart({{ $product->product_id }})"> {{-- onclick goi ham addToCart voi product_id tuong ung --}}
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ {{-- Hien thi icon gio hang va chu "Them vao gio" --}}
                        </button>
                    </div>
                </div>
            </div>
            @endforeach {{-- Ket thuc vong lap san pham moi --}}
        @else
            <div class="col-12 text-center">
                <p class="text-muted">Chưa có sản phẩm mới</p> {{-- Hien thi thong bao neu khong co san pham moi --}}
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
                    <i class="fas fa-shipping-fast fa-3x" style="color: #667eea;"></i> {{-- Icon giao hang nhanh --}}
                </div>
                <h5>Giao hàng nhanh</h5>
                <p class="text-muted">Miễn phí vận chuyển cho đơn hàng trên 500k</p> {{-- Mo ta dich vu giao hang nhanh --}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-shield-alt fa-3x" style="color: #667eea;"></i> {{-- Icon thanh toan an toan --}}
                </div>
                <h5>Thanh toán an toàn</h5>
                <p class="text-muted">Hỗ trợ đa dạng phương thức thanh toán</p> {{-- Mo ta dich vu thanh toan an toan --}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-undo-alt fa-3x" style="color: #667eea;"></i> {{-- Icon doi tra de dang --}}
                </div>
                <h5>Đổi trả dễ dàng</h5>
                <p class="text-muted">Chính sách đổi trả trong vòng 7 ngày</p> {{-- Mo ta dich vu doi tra de dang --}}
            </div>
        </div>
        <div class="col-md-3">
            <div class="text-center p-4">
                <div class="mb-3">
                    <i class="fas fa-headset fa-3x" style="color: #667eea;"></i> {{-- Icon ho tro khach hang --}}
                </div>
                <h5>Hỗ trợ 24/7</h5>
                <p class="text-muted">Đội ngũ CSKH luôn sẵn sàng hỗ trợ bạn</p> {{-- Mo ta dich vu ho tro khach hang --}}
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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ quantity: 1 })
    })
    .then(response => {
        // Kiểm tra nếu là lỗi 401 (chưa đăng nhập)
        if (response.status === 401) {
            return response.json().then(data => {
                alert(data.message || 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng!');
                window.location.href = '/login';
                throw new Error('Unauthorized');
            });
        }
        return response.json();
    })
    .then(data => {
        if(data && data.success) {
            alert(data.message);
            location.reload();
        } else if(data && !data.success) {
            alert(data.message || 'Có lỗi xảy ra!');
        }
    })
    .catch(error => {
        if (error.message !== 'Unauthorized') {
            console.error('Error:', error);
        }
    });
}
</script>
@endsection
