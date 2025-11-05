{{-- 
    Component Category Card
    Props:
    - $category: Object danh mục (bắt buộc)
--}}

<a href="{{ route('category.show', $category->category_id) }}" class="text-decoration-none">
    <div class="card text-center border-0 shadow-sm category-card">
        <div class="card-body">
            <i class="fas fa-box fa-3x mb-3 text-primary"></i>
            <h6 class="card-title mb-0">{{ $category->name }}</h6>
            <small class="text-muted">{{ $category->products_count ?? 0 }} sản phẩm</small>
        </div>
    </div>
</a>

<style>
.category-card {
    border-radius: 12px;
    transition: transform 0.3s;
}

.category-card:hover {
    transform: scale(1.05);
}

.category-card .text-primary {
    color: #667eea !important;
}
</style>

