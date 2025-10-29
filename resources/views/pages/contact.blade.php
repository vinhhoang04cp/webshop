@extends('layouts.customer')

@section('title', 'Liên hệ - WebShop')

@section('content')
{{-- Page Header --}}
<section class="page-header">
    <div class="container">
        <h1><i class="fas fa-phone"></i> Liên hệ với chúng tôi</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('home') }}">Trang chủ</a></li>
                <li class="breadcrumb-item active">Liên hệ</li>
            </ol>
        </nav>
    </div>
</section>

{{-- Contact Content --}}
<section class="container my-5">
    <div class="row">
        {{-- Contact Information --}}
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h4 class="mb-4">Thông tin liên hệ</h4>
                    
                    {{-- Địa chỉ --}}
                    <div class="contact-item mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-map-marker-alt fa-2x" style="color: var(--primary);"></i>
                            </div>
                            <div>
                                <h6>Địa chỉ</h6>
                                <p class="text-muted mb-0">
                                    123 Đường ABC, Quận 1<br>
                                    TP. Hồ Chí Minh, Việt Nam
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Hotline --}}
                    <div class="contact-item mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-phone fa-2x" style="color: var(--primary);"></i>
                            </div>
                            <div>
                                <h6>Hotline</h6>
                                <p class="text-muted mb-0">
                                    <a href="tel:19001234" class="text-decoration-none">1900-1234</a><br>
                                    <small>Hỗ trợ 24/7</small>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div class="contact-item mb-4">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-envelope fa-2x" style="color: var(--primary);"></i>
                            </div>
                            <div>
                                <h6>Email</h6>
                                <p class="text-muted mb-0">
                                    <a href="mailto:support@webshop.vn" class="text-decoration-none">support@webshop.vn</a><br>
                                    <small>Phản hồi trong 24h</small>
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Giờ làm việc --}}
                    <div class="contact-item">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fas fa-clock fa-2x" style="color: var(--primary);"></i>
                            </div>
                            <div>
                                <h6>Giờ làm việc</h6>
                                <p class="text-muted mb-0">
                                    Thứ 2 - Thứ 6: 8:00 - 20:00<br>
                                    Thứ 7 - CN: 9:00 - 18:00
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Contact Form --}}
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <h4 class="mb-4">Gửi tin nhắn cho chúng tôi</h4>

                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Form --}}
                    <form action="{{ route('contact.submit') }}" method="POST">
                        @csrf
                        <div class="row">
                            {{-- Họ và tên --}}
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       id="name" 
                                       name="name" 
                                       value="{{ old('name') }}"
                                       placeholder="Nhập họ tên của bạn"
                                       required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Email --}}
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email') }}"
                                       placeholder="email@example.com"
                                       required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Số điện thoại --}}
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Số điện thoại</label>
                                <input type="tel" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" 
                                       name="phone" 
                                       value="{{ old('phone') }}"
                                       placeholder="0123456789">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tiêu đề --}}
                            <div class="col-md-6 mb-3">
                                <label for="subject" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('subject') is-invalid @enderror" 
                                       id="subject" 
                                       name="subject" 
                                       value="{{ old('subject') }}"
                                       placeholder="Tiêu đề tin nhắn"
                                       required>
                                @error('subject')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Nội dung --}}
                            <div class="col-12 mb-3">
                                <label for="message" class="form-label">Nội dung <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('message') is-invalid @enderror" 
                                          id="message" 
                                          name="message" 
                                          rows="6" 
                                          placeholder="Nhập nội dung tin nhắn của bạn..."
                                          required>{{ old('message') }}</textarea>
                                @error('message')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Submit Button --}}
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-lg" style="border-radius: 25px;">
                                    <i class="fas fa-paper-plane"></i> Gửi tin nhắn
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Map Section --}}
<section class="container my-5">
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
        <div class="card-body p-0">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4967901856505!2d106.69750731533417!3d10.776211562177935!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f4b3330bcc1%3A0xb8b6c01f6c1b3c5!2sBen%20Thanh%20Market!5e0!3m2!1sen!2s!4v1234567890" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </div>
</section>

{{-- FAQ Section --}}
<section class="container my-5">
    <div class="row">
        <div class="col-12 text-center mb-4">
            <h2>Câu hỏi thường gặp</h2>
            <p class="text-muted">Một số câu hỏi khách hàng thường hỏi</p>
        </div>
        <div class="col-lg-8 mx-auto">
            <div class="accordion" id="faqAccordion">
                {{-- FAQ 1 --}}
                <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 8px; overflow: hidden;">
                    <h2 class="accordion-header">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                            Làm thế nào để đặt hàng?
                        </button>
                    </h2>
                    <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Bạn chỉ cần chọn sản phẩm, thêm vào giỏ hàng, điền thông tin giao hàng và thanh toán. 
                            Chúng tôi sẽ xác nhận đơn hàng qua email/SMS ngay sau đó.
                        </div>
                    </div>
                </div>

                {{-- FAQ 2 --}}
                <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 8px; overflow: hidden;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                            Thời gian giao hàng bao lâu?
                        </button>
                    </h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Thời gian giao hàng thường từ 2-5 ngày làm việc tùy theo khu vực. 
                            Đơn hàng nội thành có thể được giao trong 24h.
                        </div>
                    </div>
                </div>

                {{-- FAQ 3 --}}
                <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 8px; overflow: hidden;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                            Chính sách đổi trả như thế nào?
                        </button>
                    </h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Chúng tôi chấp nhận đổi trả trong vòng 7 ngày kể từ khi nhận hàng nếu sản phẩm 
                            có lỗi từ nhà sản xuất hoặc không đúng như mô tả.
                        </div>
                    </div>
                </div>

                {{-- FAQ 4 --}}
                <div class="accordion-item border-0 shadow-sm mb-3" style="border-radius: 8px; overflow: hidden;">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                            Có những hình thức thanh toán nào?
                        </button>
                    </h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            Chúng tôi hỗ trợ thanh toán qua VNPay, thẻ ATM/Visa/Mastercard, và COD (thanh toán khi nhận hàng).
                        </div>
                    </div>
                </div>
            </div>
        </div>
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

    .contact-item a {
        color: var(--mint-text);
    }

    .contact-item a:hover {
        color: var(--primary);
    }

    .form-control, .form-select {
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px 15px;
        transition: all 0.3s;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(0, 212, 170, 0.25);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        border: none;
        padding: 12px 30px;
        transition: transform 0.3s;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .accordion-button {
        background-color: white;
        color: var(--mint-text);
        font-weight: 600;
    }

    .accordion-button:not(.collapsed) {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
    }

    .accordion-button:focus {
        box-shadow: none;
    }
</style>
@endsection
