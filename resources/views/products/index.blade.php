@extends('layouts.customer')

@section('title', 'Sản phẩm - WebShop')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                    <li class="breadcrumb-item active">Sản phẩm</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <!-- Sidebar Filter -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                <div class="card-body">
                    <h5 class="mb-3"><i class="fas fa-filter"></i> Bộ lọc</h5>
                    
                    <!-- Price Filter -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Khoảng giá</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="price1">
                            <label class="form-check-label" for="price1">
                                Dưới 500,000₫
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="price2">
                            <label class="form-check-label" for="price2">
                                500,000₫ - 1,000,000₫
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="price3">
                            <label class="form-check-label" for="price3">
                                1,000,000₫ - 5,000,000₫
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="price4">
                            <label class="form-check-label" for="price4">
                                Trên 5,000,000₫
                            </label>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-4">
                        <h6 class="fw-bold">Danh mục</h6>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category" id="cat_all" value="" 
                                   {{ !request('category') ? 'checked' : '' }} 
                                   onchange="filterByCategory('')">
                            <label class="form-check-label" for="cat_all">
                                Tất cả
                            </label>
                        </div>
                        @foreach($categories as $category)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="category" id="cat_{{ $category->category_id }}" 
                                   value="{{ $category->category_id }}"
                                   {{ request('category') == $category->category_id ? 'checked' : '' }}
                                   onchange="filterByCategory({{ $category->category_id }})">
                            <label class="form-check-label" for="cat_{{ $category->category_id }}">
                                {{ $category->name }} ({{ $category->products_count }})
                            </label>
                        </div>
                        @endforeach
                    </div>

                    <button class="btn btn-primary w-100" style="border-radius: 25px;">
                        Áp dụng
                    </button>
                </div>
            </div>
        </div>

        <!-- Products List -->
        <div class="col-md-9">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="mb-0">
                    @if(request('q'))
                        Kết quả tìm kiếm: "{{ request('q') }}"
                    @elseif(request('category'))
                        Danh mục: {{ $categories->firstWhere('category_id', request('category'))->name ?? 'Sản phẩm' }}
                    @else
                        Tất cả sản phẩm
                    @endif
                    <small class="text-muted">({{ $products->total() }} sản phẩm)</small>
                </h3>
                <div class="d-flex align-items-center">
                    <label class="me-2">Sắp xếp:</label>
                    <select class="form-select" style="width: auto; border-radius: 20px;" onchange="sortProducts(this.value)">
                        <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                        <option value="price_asc" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Giá thấp đến cao</option>
                        <option value="price_desc" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Giá cao đến thấp</option>
                        <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Tên A-Z</option>
                        <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Tên Z-A</option>
                    </select>
                </div>
            </div>

            <div class="row g-4">
                @if($products->count() > 0)
                    @foreach($products as $product)
                    <div class="col-md-4">
                        <div class="product-card">
                            <a href="{{ route('product.show', $product->product_id) }}">
                                <img src="{{ $product->image_url ?? 'https://via.placeholder.com/300x250/667eea/ffffff?text=' . urlencode($product->name) }}" 
                                     alt="{{ $product->name }}" 
                                     class="product-image">
                            </a>
                            <div class="product-body">
                                @if($product->category)
                                    <span class="category-badge">{{ $product->category->name }}</span>
                                @endif
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
                        <h4 class="text-muted">Không tìm thấy sản phẩm nào</h4>
                        <p class="text-muted">Vui lòng thử lại với từ khóa khác</p>
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
    </div>
</div>
@endsection

@section('scripts')
<script>
function addToCart(productId) { // Hàm thêm sản phẩm vào giỏ hàng
    fetch(`/cart/add/${productId}`, { // Gửi yêu cầu đến route thêm vào giỏ hàng
        method: 'POST', // http method la post
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

function filterByCategory(categoryId) {
    const url = new URL(window.location.href);
    if(categoryId) {
        url.searchParams.set('category', categoryId);
    } else {
        url.searchParams.delete('category');
    }
    window.location.href = url.toString();
}

function sortProducts(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}
</script>
@endsection
