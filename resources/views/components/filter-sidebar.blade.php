{{-- 
    Component Filter Sidebar
    Props:
    - $categories: Collection danh mục (bắt buộc)
--}}

<div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
    <div class="card-body">
        <h5 class="mb-3"><i class="fas fa-filter"></i> Bộ lọc</h5>
        
        {{-- Price Filter --}}
        <div class="mb-4">
            <h6 class="fw-bold">Khoảng giá</h6>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="price1">
                <label class="form-check-label" for="price1">Dưới 500,000₫</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="price2">
                <label class="form-check-label" for="price2">500,000₫ - 1,000,000₫</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="price3">
                <label class="form-check-label" for="price3">1,000,000₫ - 5,000,000₫</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="price4">
                <label class="form-check-label" for="price4">Trên 5,000,000₫</label>
            </div>
        </div>

        {{-- Category Filter --}}
        <div class="mb-4">
            <h6 class="fw-bold">Danh mục</h6>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="category" id="cat_all" value="" 
                       {{ !request('category') ? 'checked' : '' }} 
                       onchange="filterByCategory('')">
                <label class="form-check-label" for="cat_all">Tất cả</label>
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

