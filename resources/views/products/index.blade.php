@extends('layouts.customer')

@section('title', 'Sản phẩm - WebShop')

@section('content')
<div class="container">
    {{-- Breadcrumb --}}
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
        {{-- Sidebar Filter --}}
        <div class="col-md-3">
            @include('components.filter-sidebar', ['categories' => $categories])
        </div>

        {{-- Products List --}}
        <div class="col-md-9">
            {{-- Header with Sort --}}
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

            {{-- Products Grid --}}
            <div class="row g-4">
                @if($products->count() > 0)
                    @foreach($products as $product)
                        <div class="col-md-4">
                            @include('components.product-card', ['product' => $product])
                        </div>
                    @endforeach
                @else
                    {{-- Empty State --}}
                    <div class="col-12 text-center py-5">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Không tìm thấy sản phẩm nào</h4>
                        <p class="text-muted">Vui lòng thử lại với từ khóa khác</p>
                    </div>
                @endif
            </div>

            {{-- Pagination --}}
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
// Filter by category
function filterByCategory(categoryId) {
    const url = new URL(window.location.href);
    if(categoryId) {
        url.searchParams.set('category', categoryId);
    } else {
        url.searchParams.delete('category');
    }
    window.location.href = url.toString();
}

// Sort products
function sortProducts(sortValue) {
    const url = new URL(window.location.href);
    url.searchParams.set('sort', sortValue);
    window.location.href = url.toString();
}
</script>
@endsection
