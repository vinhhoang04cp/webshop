@extends('layouts.customer')

@section('title', $category->name . ' - WebShop')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('products.index') }}">Sản phẩm</a></li>
                    <li class="breadcrumb-item active">{{ $category->name }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <!-- Category Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                <div class="card-body p-5 text-center">
                    <h1 class="mb-2" style="font-size: 2.5rem; font-weight: 700;">{{ $category->name }}</h1>
                    @if($category->description)
                        <p class="mb-0" style="font-size: 1.1rem;">{{ $category->description }}</p>
                    @endif
                    <p class="mt-3 mb-0">
                        <i class="fas fa-box"></i> {{ $products->total() }} sản phẩm
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Products -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="mb-0">Sản phẩm trong danh mục</h3>
                <div class="d-flex align-items-center">
                    <label class="me-2">Sắp xếp:</label>
                    <select class="form-select" style="width: auto; border-radius: 20px;" onchange="sortProducts(this.value)">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá thấp đến cao</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá cao đến thấp</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        @if($products->count() > 0)
            @foreach($products as $product)
            <div class="col-md-3">
                <div class="product-card">
                    <a href="{{ route('product.show', $product->product_id) }}">
                        <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x250/667eea/ffffff?text=' . urlencode($product->name) }}" 
                             alt="{{ $product->name }}" 
                             class="product-image">
                    </a>
                    <div class="product-body">
                        <span class="category-badge">{{ $category->name }}</span>
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
            <div class="col-12 text-center py-5">
                <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">Chưa có sản phẩm nào trong danh mục này</h4>
                <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">
                    Xem tất cả sản phẩm
                </a>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($products->hasPages())
    <div class="mt-5">
        {{ $products->links() }}
    </div>
    @endif
</div>
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

function sortProducts(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}
</script>
@endsection
