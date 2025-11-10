{{-- 
    Component Product Card
    Props:
    - $product: Object sản phẩm (bắt buộc)
    - $showBadge: Hiển thị badge "Mới" (tùy chọn, mặc định false)
--}}

@php
    $showBadge = $showBadge ?? false;
@endphp

<div class="product-card">
    {{-- Product Image --}}
    <div class="product-image-container">
        <a href="{{ route('product.show', $product->product_id) }}" class="product-link">
            <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x250/667eea/ffffff?text=' . urlencode($product->name) }}" 
                 alt="{{ $product->name }}" 
                 class="product-image">
            <div class="product-overlay">
                <i class="fas fa-eye"></i>
                <span>Xem chi tiết</span>
            </div>
        </a>
        
        @if($showBadge)
            <span class="badge bg-danger product-new-badge">
                <i class="fas fa-star me-1"></i>Mới
            </span>
        @endif
        
        <button class="btn-wishlist" title="Yêu thích">
            <i class="far fa-heart"></i>
        </button>
    </div>

<style>
@keyframes bounceIn {
    0% {
        opacity: 0;
        transform: scale(0.3);
    }
    50% {
        opacity: 1;
        transform: scale(1.05);
    }
    70% {
        transform: scale(0.9);
    }
    100% {
        transform: scale(1);
    }
}

.product-new-badge {
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.05);
    }
}
</style>

    {{-- Product Body --}}
    <div class="product-body">
        <div class="product-header-info">
            {{-- Category Badge --}}
            @if($product->category)
                <span class="category-badge">
                    <i class="fas fa-tag"></i> {{ $product->category->name }}
                </span>
            @endif
            
            {{-- Rating --}}
            @if(method_exists($product, 'averageRating'))
                <div class="product-rating-inline">
                    @include('components.rating-stars', ['rating' => $product->averageRating()])
                </div>
            @endif
        </div>
        
        {{-- Product Title --}}
        <a href="{{ route('product.show', $product->product_id) }}" class="text-decoration-none">
            <h5 class="product-title">{{ $product->name }}</h5>
        </a>

        {{-- Price --}}
        <div class="product-price-wrapper mb-3">
            @include('components.product-price', ['product' => $product])
        </div>
        
        {{-- Created Time (if showing badge) --}}
        @if($showBadge && isset($product->created_at))
            <div class="product-time-badge">
                <i class="fas fa-clock"></i> {{ $product->created_at->diffForHumans() }}
            </div>
        @endif
        
        {{-- Add to Cart Form --}}
        <form action="{{ route('cart.add', $product->product_id) }}" method="POST" class="product-action-form">
            @csrf
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="btn-add-cart">
                <span class="btn-icon">
                    <i class="fas fa-cart-plus"></i>
                </span>
                <span class="btn-text">Thêm vào giỏ</span>
            </button>
        </form>
    </div>
</div>

