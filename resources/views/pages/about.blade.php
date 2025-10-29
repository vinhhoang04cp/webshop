@extends('layouts.customer')

@section('title', 'Về chúng tôi - WebShop')

@section('content')
{{-- Page Header --}}
<section class="page-header">
    <div class="container">
        <h1><i class="fas fa-info-circle"></i> Về chúng tôi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item active">Về chúng tôi</li>
            </ol>
        </nav>
    </div>
</section>

{{-- About Content --}}
<section class="container my-5">
    {{-- Story Section --}}
    <div class="row align-items-center mb-5">
        <div class="col-md-6">
            <img src="https://m.yodycdn.com/blog/hinh-nen-thien-nhien-4k-yody-vn-11.jpg" 
                 alt="Về WebShop" 
                 class="img-fluid rounded shadow">
        </div>
        <div class="col-md-6">
            <h2 class="mb-4">Câu chuyện của chúng tôi</h2>
            <p class="text-muted" style="line-height: 1.8;">
                WebShop được thành lập với sứ mệnh mang đến cho khách hàng những sản phẩm chất lượng cao 
                với giá cả hợp lý nhất. Chúng tôi cam kết cung cấp trải nghiệm mua sắm trực tuyến tuyệt vời, 
                an toàn và tiện lợi.
            </p>
            <p class="text-muted" style="line-height: 1.8;">
                Với đội ngũ nhân viên tận tâm và hệ thống công nghệ hiện đại, chúng tôi không ngừng nỗ lực 
                để đáp ứng mọi nhu cầu mua sắm của bạn.
            </p>
        </div>
    </div>

    {{-- Core Values --}}
    <div class="row mb-5">
        <div class="col-12 text-center mb-4">
            <h2>Giá trị cốt lõi</h2>
            <p class="text-muted">Những giá trị mà chúng tôi luôn hướng tới</p>
        </div>

        {{-- Uy tín --}}
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-shield-alt fa-3x" style="color: var(--primary);"></i>
                    </div>
                    <h4>Uy tín</h4>
                    <p class="text-muted">
                        Chúng tôi đặt uy tín lên hàng đầu, cam kết mang đến sản phẩm chính hãng, 
                        chất lượng đảm bảo.
                    </p>
                </div>
            </div>
        </div>

        {{-- Tận tâm --}}
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-heart fa-3x" style="color: #ef4444;"></i>
                    </div>
                    <h4>Tận tâm</h4>
                    <p class="text-muted">
                        Đội ngũ nhân viên nhiệt tình, chuyên nghiệp, luôn sẵn sàng hỗ trợ khách hàng 24/7.
                    </p>
                </div>
            </div>
        </div>

        {{-- Đổi mới --}}
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body text-center p-4">
                    <div class="mb-3">
                        <i class="fas fa-rocket fa-3x" style="color: #8b5cf6;"></i>
                    </div>
                    <h4>Đổi mới</h4>
                    <p class="text-muted">
                        Không ngừng cải tiến công nghệ và dịch vụ để mang đến trải nghiệm mua sắm tốt nhất.
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="row text-center mb-5 p-5" style="background: linear-gradient(135deg, var(--primary), var(--secondary)); border-radius: 16px;">
        <div class="col-md-3 mb-3">
            <h2 class="text-white mb-0"><i class="fas fa-users"></i> 10,000+</h2>
            <p class="text-white mb-0">Khách hàng tin tưởng</p>
        </div>
        <div class="col-md-3 mb-3">
            <h2 class="text-white mb-0"><i class="fas fa-box"></i> 5,000+</h2>
            <p class="text-white mb-0">Sản phẩm đa dạng</p>
        </div>
        <div class="col-md-3 mb-3">
            <h2 class="text-white mb-0"><i class="fas fa-star"></i> 4.8/5</h2>
            <p class="text-white mb-0">Đánh giá trung bình</p>
        </div>
        <div class="col-md-3 mb-3">
            <h2 class="text-white mb-0"><i class="fas fa-shipping-fast"></i> 100%</h2>
            <p class="text-white mb-0">Giao hàng đúng hạn</p>
        </div>
    </div>

    {{-- Why Choose Us --}}
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h2>Tại sao chọn WebShop?</h2>
        </div>

        {{-- Sản phẩm chính hãng --}}
        <div class="col-md-6 mb-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-check-circle fa-2x" style="color: var(--primary);"></i>
                </div>
                <div>
                    <h5>Sản phẩm chính hãng 100%</h5>
                    <p class="text-muted">
                        Tất cả sản phẩm đều được nhập từ các nhà phân phối chính thức, đảm bảo nguồn gốc xuất xứ.
                    </p>
                </div>
            </div>
        </div>

        {{-- Giá cả cạnh tranh --}}
        <div class="col-md-6 mb-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-check-circle fa-2x" style="color: var(--primary);"></i>
                </div>
                <div>
                    <h5>Giá cả cạnh tranh</h5>
                    <p class="text-muted">
                        Cam kết mang đến mức giá tốt nhất thị trường với nhiều chương trình khuyến mãi hấp dẫn.
                    </p>
                </div>
            </div>
        </div>

        {{-- Giao hàng nhanh chóng --}}
        <div class="col-md-6 mb-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-check-circle fa-2x" style="color: var(--primary);"></i>
                </div>
                <div>
                    <h5>Giao hàng nhanh chóng</h5>
                    <p class="text-muted">
                        Hệ thống giao hàng toàn quốc, cam kết giao hàng đúng hạn hoặc hoàn tiền.
                    </p>
                </div>
            </div>
        </div>

        {{-- Hỗ trợ 24/7 --}}
        <div class="col-md-6 mb-4">
            <div class="d-flex">
                <div class="me-3">
                    <i class="fas fa-check-circle fa-2x" style="color: var(--primary);"></i>
                </div>
                <div>
                    <h5>Hỗ trợ 24/7</h5>
                    <p class="text-muted">
                        Đội ngũ chăm sóc khách hàng luôn sẵn sàng giải đáp mọi thắc mắc của bạn.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- CTA Section --}}
<section class="container my-5">
    <div class="text-center p-5" style="background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 16px;">
        <h2 class="text-white mb-3">Bắt đầu mua sắm ngay hôm nay!</h2>
        <p class="text-white mb-4">Khám phá hàng ngàn sản phẩm chất lượng với giá tốt nhất</p>
        <a href="{{ route('products.index') }}" class="btn btn-light btn-lg" style="border-radius: 25px;">
            <i class="fas fa-shopping-bag"></i> Mua sắm ngay
        </a>
    </div>
</section>

<style>
    .page-header {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        padding: 60px 0 40px;
        margin-bottom: 40px;
    }

    .page-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 15px;
    }

    .breadcrumb {
        background: transparent;
        margin-bottom: 0;
        padding: 0;
    }

    .breadcrumb-item a {
        color: rgba(255,255,255,0.8);
        text-decoration: none;
    }

    .breadcrumb-item a:hover {
        color: white;
    }

    .breadcrumb-item.active {
        color: white;
    }

    .breadcrumb-item + .breadcrumb-item::before {
        color: rgba(255,255,255,0.6);
    }
</style>
@endsection
