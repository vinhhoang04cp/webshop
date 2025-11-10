{{-- 
    Component Category Card
    Props:
    - $category: Object danh mục (bắt buộc)
--}}

<a href="{{ route('category.show', $category->category_id) }}" class="text-decoration-none">
    <div class="card text-center border-0 category-card">
        <div class="category-glow"></div>
        <div class="card-body">
            <div class="category-icon-wrapper">
                <div class="icon-bg-effect"></div>
                <i class="fas fa-box fa-3x text-primary"></i>
                <div class="icon-particles">
                    <span class="particle"></span>
                    <span class="particle"></span>
                    <span class="particle"></span>
                </div>
            </div>
            <h6 class="card-title mb-2">{{ $category->name }}</h6>
            <div class="category-count-wrapper">
                <i class="fas fa-cube"></i>
                <small class="category-count">{{ $category->products_count ?? 0 }} sản phẩm</small>
            </div>
            <div class="category-arrow">
                <i class="fas fa-arrow-right"></i>
            </div>
        </div>
    </div>
</a>

<style>
.category-card {
    border-radius: 20px;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    box-shadow: 0 4px 15px rgba(0, 212, 170, 0.1);
    position: relative;
    overflow: hidden;
    border: 2px solid rgba(0, 212, 170, 0.12);
}

.category-glow {
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(0, 212, 170, 0.15) 0%, transparent 70%);
    opacity: 0;
    transition: opacity 0.4s ease, transform 0.4s ease;
    pointer-events: none;
}

.category-card:hover .category-glow {
    opacity: 1;
    transform: scale(1.2) rotate(30deg);
}

.category-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, #00d4aa, #26d0ce);
    opacity: 0;
    transition: opacity 0.4s ease;
}

.category-card:hover::before {
    opacity: 0.06;
}

.category-card:hover {
    transform: translateY(-12px) scale(1.05);
    box-shadow: 0 15px 40px rgba(0, 212, 170, 0.25);
    border-color: #00d4aa;
}

.category-card .card-body {
    position: relative;
    z-index: 1;
    padding: 30px 20px;
}

.category-icon-wrapper {
    width: 90px;
    height: 90px;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(0, 212, 170, 0.12), rgba(38, 208, 206, 0.12));
    border-radius: 50%;
    transition: all 0.4s ease;
    position: relative;
}

.icon-bg-effect {
    position: absolute;
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, rgba(0, 212, 170, 0.3), rgba(38, 208, 206, 0.3));
    border-radius: 50%;
    transform: scale(0);
    transition: transform 0.4s ease;
}

.category-card:hover .icon-bg-effect {
    transform: scale(1.3);
    opacity: 0;
}

.icon-particles {
    position: absolute;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.particle {
    position: absolute;
    width: 6px;
    height: 6px;
    background: linear-gradient(135deg, #00d4aa, #26d0ce);
    border-radius: 50%;
    opacity: 0;
    transition: all 0.4s ease;
}

.particle:nth-child(1) {
    top: 10%;
    left: 50%;
}

.particle:nth-child(2) {
    top: 50%;
    right: 10%;
}

.particle:nth-child(3) {
    bottom: 10%;
    left: 50%;
}

.category-card:hover .particle {
    opacity: 1;
    animation: particleFloat 1.5s ease-in-out infinite;
}

@keyframes particleFloat {
    0%, 100% {
        transform: translate(0, 0);
    }
    50% {
        transform: translate(10px, -10px);
    }
}

.category-card:hover .category-icon-wrapper {
    transform: rotate(15deg) scale(1.15);
    background: linear-gradient(135deg, rgba(0, 212, 170, 0.2), rgba(38, 208, 206, 0.2));
    box-shadow: 0 8px 20px rgba(0, 212, 170, 0.3);
}

.category-card .text-primary {
    background: linear-gradient(135deg, #00d4aa, #26d0ce);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    transition: all 0.3s ease;
    position: relative;
    z-index: 1;
}

.category-card:hover .text-primary {
    transform: scale(1.15);
    filter: drop-shadow(0 2px 4px rgba(0, 212, 170, 0.3));
}

.category-card .card-title {
    font-weight: 700;
    color: #134e4a;
    font-size: 1.05rem;
    transition: color 0.3s ease;
    margin-bottom: 12px;
}

.category-card:hover .card-title {
    color: #00d4aa;
}

.category-count-wrapper {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    background: linear-gradient(135deg, rgba(0, 212, 170, 0.08), rgba(38, 208, 206, 0.08));
    border-radius: 15px;
    transition: all 0.3s ease;
}

.category-count-wrapper i {
    color: #00d4aa;
    font-size: 0.8rem;
}

.category-count {
    color: #5f8a8b;
    font-weight: 600;
    font-size: 0.85rem;
}

.category-card:hover .category-count-wrapper {
    background: linear-gradient(135deg, rgba(0, 212, 170, 0.15), rgba(38, 208, 206, 0.15));
    transform: scale(1.05);
}

.category-arrow {
    position: absolute;
    bottom: 15px;
    right: 15px;
    width: 35px;
    height: 35px;
    background: linear-gradient(135deg, #00d4aa, #26d0ce);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transform: scale(0) rotate(-45deg);
    transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.category-arrow i {
    color: white;
    font-size: 0.9rem;
}

.category-card:hover .category-arrow {
    opacity: 1;
    transform: scale(1) rotate(0deg);
}
</style>

