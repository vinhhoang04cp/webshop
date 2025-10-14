@extends('layouts.customer')

@section('title', $product->name . ' - WebShop')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                    @if($product->category)
                        <li class="breadcrumb-item">
                            <a href="{{ route('category.show', $product->category->category_id) }}">
                                {{ $product->category->name }}
                            </a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active">{{ $product->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-5">
        <!-- Product Image -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                <img src="{{ $product->image_url ?? 'https://via.placeholder.com/500x500/667eea/ffffff?text=' . urlencode($product->name) }}" 
                     alt="{{ $product->name }}" 
                     class="img-fluid"
                     style="width: 100%; height: 500px; object-fit: cover;">
            </div>
        </div>

        <!-- Product Info -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    @if($product->category)
                        <span class="category-badge mb-3">{{ $product->category->name }}</span>
                    @endif
                    
                    <h1 class="mb-3" style="font-size: 2rem; font-weight: 700;">{{ $product->name }}</h1>
                    
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-warning me-3">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                            <span class="text-muted ms-2">(4.0)</span>
                        </div>
                        <span class="text-muted">|</span>
                        <span class="ms-3 text-muted">
                            <i class="fas fa-box"></i> 
                            Kho: 
                            @if($product->inventory)
                                <strong>{{ $product->inventory->quantity }}</strong> sản phẩm
                            @else
                                <strong class="text-danger">Hết hàng</strong>
                            @endif
                        </span>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h2 class="text-primary mb-0" style="font-size: 2.5rem; font-weight: 700;">
                            {{ number_format($product->price, 0, ',', '.') }}₫
                        </h2>
                    </div>

                    <div class="mb-4">
                        <h5>Mô tả sản phẩm:</h5>
                        <p class="text-muted">{{ $product->description ?? 'Chưa có mô tả cho sản phẩm này.' }}</p>
                    </div>

                    @if($product->details)
                        <div class="mb-4">
                            <h5>Thông tin chi tiết:</h5>
                            <ul class="list-unstyled">
                                @if($product->details->color)
                                    <li class="mb-2"><strong>Màu sắc:</strong> {{ $product->details->color }}</li>
                                @endif
                                @if($product->details->size)
                                    <li class="mb-2"><strong>Kích thước:</strong> {{ $product->details->size }}</li>
                                @endif
                                @if($product->details->weight)
                                    <li class="mb-2"><strong>Trọng lượng:</strong> {{ $product->details->weight }}</li>
                                @endif
                                @if($product->details->material)
                                    <li class="mb-2"><strong>Chất liệu:</strong> {{ $product->details->material }}</li>
                                @endif
                            </ul>
                        </div>
                    @endif

                    <div class="mb-4">
                        <label class="mb-2"><strong>Số lượng:</strong></label>
                        <div class="input-group" style="width: 150px;">
                            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQty()">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" id="quantity" class="form-control text-center" value="1" min="1">
                            <button class="btn btn-outline-secondary" type="button" onclick="increaseQty()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-primary btn-lg" style="border-radius: 25px; padding: 12px;" 
                                onclick="addToCart({{ $product->product_id }})">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                        </button>
                        <button class="btn btn-outline-primary btn-lg" style="border-radius: 25px;">
                            <i class="fas fa-heart"></i> Thêm vào yêu thích
                        </button>
                    </div>

                    <div class="alert alert-info mt-3" style="border-radius: 10px;">
                        <i class="fas fa-truck"></i> Miễn phí vận chuyển cho đơn hàng trên 500k
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <section class="mb-5">
        <h2 class="section-title">Sản phẩm liên quan</h2>
        <div class="row g-4">
            @foreach($relatedProducts as $related)
            <div class="col-md-3">
                <div class="product-card">
                    <a href="{{ route('product.show', $related->product_id) }}">
                        <img src="{{ $related->image_url ?? 'https://via.placeholder.com/300x250/764ba2/ffffff?text=' . urlencode($related->name) }}" 
                             alt="{{ $related->name }}" 
                             class="product-image">
                    </a>
                    <div class="product-body">
                        @if($related->category)
                            <span class="category-badge">{{ $related->category->name }}</span>
                        @endif
                        <a href="{{ route('product.show', $related->product_id) }}" class="text-decoration-none">
                            <h5 class="product-title">{{ $related->name }}</h5>
                        </a>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="product-price">{{ number_format($related->price, 0, ',', '.') }}₫</span>
                        </div>
                        <button class="btn-add-cart" onclick="addToCart({{ $related->product_id }})">
                            <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection

@section('scripts')
<script>
function increaseQty() {
    const qtyInput = document.getElementById('quantity');
    qtyInput.value = parseInt(qtyInput.value) + 1;
}

function decreaseQty() {
    const qtyInput = document.getElementById('quantity');
    if(parseInt(qtyInput.value) > 1) {
        qtyInput.value = parseInt(qtyInput.value) - 1;
    }
}

function addToCart(productId) {
    const quantity = document.getElementById('quantity').value;
    
    fetch(`/cart/add/${productId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ quantity: parseInt(quantity) })
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
