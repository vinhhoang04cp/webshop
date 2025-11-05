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
    <div style="position: relative;">
        <a href="{{ route('product.show', $product->product_id) }}">
            <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x250/667eea/ffffff?text=' . urlencode($product->name) }}" 
                 alt="{{ $product->name }}" 
                 class="product-image">
        </a>
        
        @if($showBadge)
            <span class="badge bg-danger" style="position: absolute; top: 10px; right: 10px;">Mới</span>
        @endif
    </div>

    {{-- Product Body --}}
    <div class="product-body">
        {{-- Category Badge --}}
        @if($product->category)
            <span class="category-badge">{{ $product->category->name }}</span>
        @endif
        
        {{-- Product Title --}}
        <a href="{{ route('product.show', $product->product_id) }}" class="text-decoration-none">
            <h5 class="product-title">{{ $product->name }}</h5>
        </a>

        {{-- Price --}}
        <div class="mb-2">
            @include('components.product-price', ['product' => $product])
        </div>

        {{-- Rating --}}
        @if(method_exists($product, 'averageRating'))
            @include('components.rating-stars', ['rating' => $product->averageRating()])
        @endif
        
        {{-- Created Time (if showing badge) --}}
        @if($showBadge && isset($product->created_at))
            <small class="text-muted d-block mt-2">{{ $product->created_at->diffForHumans() }}</small>
        @endif
        
        {{-- Add to Cart Form --}}
        <form action="{{ route('cart.add', $product->product_id) }}" method="POST">
            @csrf
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="btn-add-cart">
                <i class="fas fa-cart-plus"></i> Thêm vào giỏ
            </button>
        </form>
    </div>
</div>

