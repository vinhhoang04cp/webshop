@extends('layouts.customer') {{-- Ke thua layout chính --}}

@section('title', $product->name . ' - WebShop') {{-- Tiêu đề trang --}}

@section('content') {{-- Nội dung chính --}}
<div class="container">
    {{-- Hiển thị thông báo --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                    @if($product->category) {{-- Kiểm tra nếu sản phẩm có danh mục --}}
                        <li class="breadcrumb-item"> 
                            <a href="{{ route('category.show', $product->category->category_id) }}"> 
                                {{ $product->category->name }} {{-- Hiển thị tên danh mục --}}
                            </a>
                        </li>
                    @endif 
                    <li class="breadcrumb-item active">{{ $product->name }}</li> {{-- Tên sản phẩm --}}
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-5"> 
        <!-- Product Image -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                {{-- $product->image_url hien thi anh san pham qua link url neu khong co thi hien thi hinh mac dinh --}}
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
                    @if($product->category) {{-- Kiểm tra nếu sản phẩm có danh mục --}}
                        <span class="category-badge mb-3">{{ $product->category->name }}</span> {{-- Hiển thị tên danh mục --}}
                    @endif
                    
                    <h1 class="mb-3" style="font-size: 2rem; font-weight: 700;">{{ $product->name }}</h1> {{-- Tên sản phẩm --}}
                    
                    <div class="d-flex align-items-center mb-3"> {{-- Đánh giá sao giả định --}}
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
                            {{-- $product->inventory hien thi thong tin kho --}}
                            @if($product->inventory)
                                <strong>{{ $product->inventory->quantity }}</strong> sản phẩm
                            @else
                                <strong class="text-danger">Hết hàng</strong> {{-- Neu khong co thong tin kho thi hien thi het hang --}}
                            @endif
                        </span>
                    </div>

                    <hr>

                    <div class="mb-4">
                        <h2 class="text-primary mb-0" style="font-size: 2.5rem; font-weight: 700;">
                            {{ number_format($product->price, 0, ',', '.') }}₫ {{-- Giá sản phẩm đã được định dạng --}}
                        </h2>
                    </div>

                    <div class="mb-4">
                        <h5>Mô tả sản phẩm:</h5>
                        <p class="text-muted">{{ $product->description ?? 'Chưa có mô tả cho sản phẩm này.' }}</p>
                        {{-- $product->description hien thi mo ta san pham --}}
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

                    <form action="{{ route('cart.add', $product->product_id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="mb-2"><strong>Số lượng:</strong></label>
                            <div class="input-group" style="width: 150px;">
                                <input type="number" name="quantity" class="form-control text-center" value="1" min="1" max="{{ $product->inventory ? $product->inventory->quantity : 1 }}">
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 25px; padding: 12px;">
                                <i class="fas fa-cart-plus"></i> Thêm vào giỏ hàng
                            </button>
                        </div>
                    </form>

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
                        <form action="{{ route('cart.add', $related->product_id) }}" method="POST" style="display: inline;">
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
    </section>
    @endif
</div>
@endsection
