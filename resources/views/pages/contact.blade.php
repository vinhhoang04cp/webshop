@extends('layouts.customer')

@section('title', 'Liên hệ - WebShop')

@section('content')
@include('components.page-header', [
    'title' => 'Liên hệ với chúng tôi',
    'icon' => 'fas fa-phone',
    'breadcrumbs' => [['text' => 'Liên hệ']]
])

{{-- Contact Content --}}
<section class="container my-5">
    <div class="row justify-content-center">
        {{-- Contact Information --}}
        <div class="col-lg-6 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 12px;">
                <div class="card-body p-5">
                    <h4 class="mb-4 text-center">Thông tin liên hệ</h4>
                    
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
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
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
