@extends('layouts.customer')

@section('title', 'Sản phẩm khuyến mãi - WebShop')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Sản phẩm khuyến mãi</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12 text-center">
            <h1 class="section-title">
                <i class="fas fa-fire text-danger"></i> 
                Sản Phẩm Khuyến Mãi Hot
            </h1>
            <p class="text-muted">Giảm giá sốc - Không thể bỏ lỡ!</p>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="row">
        @if($products->count() > 0)
            <div class="col-12 mb-3">
                <div class="alert alert-info" style="border-radius: 12px;">
                    <i class="fas fa-tag"></i> 
                    Tìm thấy <strong>{{ $products->total() }}</strong> sản phẩm đang khuyến mãi
                </div>
            </div>

            <div class="row g-4">
                @foreach($products as $product)
                <div class="col-md-4 col-lg-3">
                    <div class="product-card">
                        <div style="position: relative;">
                            <a href="{{ route('product.show', $product->product_id) }}">
                                <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x250/667eea/ffffff?text=' . urlencode($product->name) }}" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            </a>
                            
                            @php
                                $discountPercent = round((($product->original_price - $product->price) / $product->original_price) * 100);
                            @endphp
                            
                            <!-- Badge giảm giá -->
                            <span class="badge bg-danger" style="position: absolute; top: 10px; right: 10px; font-size: 1rem; padding: 8px 12px;">
                                -{{ $discountPercent }}%
                            </span>
                        </div>
                        
                        <div class="product-body">
                            @if($product->category)
                                <span class="category-badge">{{ $product->category->name }}</span>
                            @endif
                            
                            <a href="{{ route('product.show', $product->product_id) }}" class="text-decoration-none">
                                <h5 class="product-title">{{ $product->name }}</h5>
                            </a>
                            
                            <!-- Giá -->
                            <div class="mb-2">
                                <div class="text-muted small text-decoration-line-through mb-1">
                                    {{ number_format($product->original_price, 0, ',', '.') }}₫
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="product-price text-danger" style="font-size: 1.4rem;">
                                        {{ number_format($product->price, 0, ',', '.') }}₫
                                    </span>
                                </div>
                                <div class="text-success small mt-1">
                                    <i class="fas fa-arrow-down"></i> 
                                    Tiết kiệm {{ number_format($product->original_price - $product->price, 0, ',', '.') }}₫
                                </div>
                            </div>
                            
                            <!-- Rating -->
                            @include('components.rating-stars', ['rating' => $product->averageRating()])
                            
                            <!-- Form thêm vào giỏ hàng -->
                            <form action="{{ route('cart.add', $product->product_id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="quantity" value="1">
                                <button type="submit" class="btn-add-cart">
                                    <i class="fas fa-cart-plus"></i> Thêm vào giỏ
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
            <div class="mt-5">
                {{ $products->links() }}
            </div>
            @endif
        @else
            <div class="col-12 text-center py-5">
                <i class="fas fa-tag fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Hiện tại chưa có sản phẩm khuyến mãi</h4>
                <p class="text-muted">Vui lòng quay lại sau hoặc xem các sản phẩm khác</p>
                <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">
                    <i class="fas fa-shopping-bag"></i> Xem tất cả sản phẩm
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

@section('styles')
<style>
    .section-title {
        font-size: 2.5rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1rem;
    }
    
    .product-card {
        transition: all 0.3s ease;
        border-radius: 12px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .product-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }
    
    .product-image {
        width: 100%;
        height: 250px;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .product-card:hover .product-image {
        transform: scale(1.05);
    }
    
    .product-body {
        padding: 15px;
    }
    
    .category-badge {
        background: linear-gradient(135deg, #667eea, #764ba2);
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        display: inline-block;
        margin-bottom: 8px;
    }
    
    .product-title {
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin: 8px 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 48px;
    }
    
    .product-title:hover {
        color: var(--primary);
    }
</style>
@endsection
