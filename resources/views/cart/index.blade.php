@extends('layouts.customer')

@section('title', 'Giỏ hàng - WebShop')

@section('content')
<div class="container">
    <h2 class="section-title">Giỏ hàng của bạn</h2>

    <!-- Hiển thị thông báo -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 10px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 10px;">
            <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <!-- Cart Items -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Đơn giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($cartItems->count() > 0)
                                    @foreach($cartItems as $item)
                                        @include('components.cart-item', ['item' => $item])
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">Giỏ hàng trống</h5>
                                            <a href="{{ route('products.index') }}" class="btn btn-primary mt-3">
                                                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                                            </a>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-3">
                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-body">
                    <h5 class="mb-4">Tóm tắt đơn hàng</h5>
                    
                    @php
                        $subtotal = $cart->totalPrice() ?? 0;
                        $shippingFee = $subtotal >= 500000 ? 0 : 30000;
                        $total = $subtotal + $shippingFee;
                    @endphp
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Tạm tính:</span>
                        <span id="subtotal">{{ number_format($subtotal, 0, ',', '.') }}₫</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>Phí vận chuyển:</span>
                        <span id="shipping-fee">{{ number_format($shippingFee, 0, ',', '.') }}₫</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <strong>Tổng cộng:</strong>
                        <strong class="text-primary" style="font-size: 1.3rem;" id="total">
                            {{ number_format($total, 0, ',', '.') }}₫
                        </strong>
                    </div>

                    @if($cartItems->count() > 0)
                        @include('components.checkout-form')
                        
                        <form action="{{ route('cart.clear') }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100 mb-3" style="border-radius: 25px;" 
                                    onclick="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')">
                                <i class="fas fa-trash"></i> Xóa toàn bộ giỏ hàng
                            </button>
                        </form>
                    @endif

                    @if($cartItems->count() == 0)
                        <div class="alert alert-info" style="border-radius: 10px;">
                            <i class="fas fa-info-circle"></i> Miễn phí vận chuyển cho đơn hàng trên 500k
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    #checkout-form .form-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 8px;
    }
    
    #checkout-form .form-control {
        border-radius: 8px;
        border: 1.5px solid #e0e0e0;
        padding: 10px 15px;
        transition: all 0.3s ease;
    }
    
    #checkout-form .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
    }
    
    #checkout-form .form-label i {
        color: #667eea;
        margin-right: 5px;
    }
    
    #checkout-form textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }
</style>
@endsection

@section('scripts')
<script>
    // Hàm chọn phương thức thanh toán
    function selectPaymentMethod(method) {
        // Remove active class from all
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('active');
        });
        
        // Add active class to selected
        if (method === 'cod') {
            document.getElementById('payment_cod').checked = true;
            document.getElementById('payment_cod').closest('.payment-method').classList.add('active');
            document.getElementById('checkout-btn-text').textContent = 'Đặt hàng (COD)';
        } else if (method === 'vnpay') {
            document.getElementById('payment_vnpay').checked = true;
            document.getElementById('payment_vnpay').closest('.payment-method').classList.add('active');
            document.getElementById('checkout-btn-text').textContent = 'Thanh toán với VNPay';
        }
    }
    
    // Initialize on load
    document.addEventListener('DOMContentLoaded', function() {
        // Set initial active state
        selectPaymentMethod('cod');
        
        // Add click listeners to radio buttons
        document.getElementById('payment_cod')?.addEventListener('change', function() {
            if (this.checked) selectPaymentMethod('cod');
        });
        
        document.getElementById('payment_vnpay')?.addEventListener('change', function() {
            if (this.checked) selectPaymentMethod('vnpay');
        });
    });
</script>
@endsection