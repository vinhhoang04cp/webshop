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
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $item->product->image_url ?? 'https://via.placeholder.com/80x80/667eea/ffffff?text=' . urlencode($item->product->name) }}" 
                                                     alt="{{ $item->product->name }}" 
                                                     style="border-radius: 8px; margin-right: 15px; width: 80px; height: 80px; object-fit: cover;">
                                                <div>
                                                    <h6 class="mb-0">
                                                        <a href="{{ route('product.show', $item->product->product_id) }}" class="text-decoration-none text-dark">
                                                            {{ $item->product->name }}
                                                        </a>
                                                    </h6>
                                                    @if($item->product->category)
                                                        <small class="text-muted">{{ $item->product->category->name }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">{{ number_format($item->price ?? $item->product->price ?? 0, 0, ',', '.') }}₫</td>
                                        <td class="align-middle">
                                            <!-- Form cập nhật số lượng -->
                                            <div class="d-flex align-items-center gap-2" style="width: 180px;">
                                                <!-- Giảm số lượng -->
                                                <form action="{{ route('cart.update', $item->cart_item_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="quantity" value="{{ max(1, $item->quantity - 1) }}">
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm" {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                                                        <i class="fas fa-minus"></i>
                                                    </button>
                                                </form>
                                                
                                                <!-- Hiển thị số lượng -->
                                                <span class="fw-bold mx-2" style="min-width: 30px; text-align: center;">{{ $item->quantity }}</span>
                                                
                                                <!-- Tăng số lượng -->
                                                <form action="{{ route('cart.update', $item->cart_item_id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('PUT')
                                                    <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                                    <button type="submit" class="btn btn-outline-secondary btn-sm">
                                                        <i class="fas fa-plus"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                        <td class="fw-bold align-middle">
                                            {{ number_format(($item->price ?? $item->product->price ?? 0) * $item->quantity, 0, ',', '.') }}₫
                                        </td>
                                        <td class="align-middle">
                                            <!-- Form xóa sản phẩm -->
                                            <form action="{{ route('cart.remove', $item->cart_item_id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-link text-danger p-0" title="Xóa sản phẩm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
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
                        <!-- Form thông tin thanh toán COD -->
                        <form action="{{ route('cart.checkout') }}" method="POST" id="checkout-form">
                            @csrf
                            
                            <div class="mb-3">
                                <label for="shipping_name" class="form-label">
                                    <i class="fas fa-user"></i> Họ và tên người nhận
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control @error('shipping_name') is-invalid @enderror" 
                                       id="shipping_name" 
                                       name="shipping_name" 
                                       value="{{ old('shipping_name', Auth::user()->name) }}"
                                       placeholder="Nhập họ và tên"
                                       required>
                                @error('shipping_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="shipping_phone" class="form-label">
                                    <i class="fas fa-phone"></i> Số điện thoại
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="tel" 
                                       class="form-control @error('shipping_phone') is-invalid @enderror" 
                                       id="shipping_phone" 
                                       name="shipping_phone" 
                                       value="{{ old('shipping_phone', Auth::user()->phone) }}"
                                       placeholder="Nhập số điện thoại"
                                       required>
                                @error('shipping_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Địa chỉ giao hàng
                                    <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('shipping_address') is-invalid @enderror" 
                                          id="shipping_address" 
                                          name="shipping_address" 
                                          rows="3" 
                                          placeholder="Nhập địa chỉ chi tiết (số nhà, tên đường, phường/xã, quận/huyện, tỉnh/thành phố)"
                                          required>{{ old('shipping_address', Auth::user()->address) }}</textarea>
                                @error('shipping_address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="note" class="form-label">
                                    <i class="fas fa-comment"></i> Ghi chú đơn hàng
                                </label>
                                <textarea class="form-control" 
                                          id="note" 
                                          name="note" 
                                          rows="2" 
                                          placeholder="Ghi chú thêm về đơn hàng (không bắt buộc)">{{ old('note') }}</textarea>
                            </div>

                            <!-- Chọn phương thức thanh toán -->
                            <div class="mb-4">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-credit-card"></i> Chọn phương thức thanh toán
                                    <span class="text-danger">*</span>
                                </label>
                                
                                <!-- Thanh toán COD -->
                                <div class="payment-method mb-3" style="border: 2px solid #e0e0e0; border-radius: 10px; padding: 15px; cursor: pointer; transition: all 0.3s;" onclick="selectPaymentMethod('cod')">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                                        <label class="form-check-label w-100" for="payment_cod" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-money-bill-wave fa-2x text-warning me-3"></i>
                                                <div>
                                                    <strong>Thanh toán khi nhận hàng (COD)</strong>
                                                    <p class="mb-0 small text-muted">Bạn sẽ thanh toán bằng tiền mặt khi nhận được hàng</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Thanh toán VNPay -->
                                <div class="payment-method mb-3" style="border: 2px solid #e0e0e0; border-radius: 10px; padding: 15px; cursor: pointer; transition: all 0.3s;" onclick="selectPaymentMethod('vnpay')">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" id="payment_vnpay" value="vnpay">
                                        <label class="form-check-label w-100" for="payment_vnpay" style="cursor: pointer;">
                                            <div class="d-flex align-items-center">
                                                <div class="me-3" style="width: 50px; height: 50px; background: linear-gradient(135deg, #0088cc, #00aaff); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="fas fa-credit-card fa-lg text-white"></i>
                                                </div>
                                                <div>
                                                    <strong>Thanh toán Online qua VNPay</strong>
                                                    <p class="mb-0 small text-muted">Thanh toán bằng thẻ ATM, Visa, MasterCard qua cổng VNPay</p>
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary w-100 mb-3" style="padding: 12px; border-radius: 25px; font-size: 1.1rem;">
                                <i class="fas fa-check"></i> <span id="checkout-btn-text">Đặt hàng (COD)</span>
                            </button>
                        </form>
                        
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
    /* Styling cho form thông tin giao hàng */
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
    
    #checkout-form .text-danger {
        font-weight: 600;
    }
    
    #checkout-form textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }
    
    .alert-warning {
        background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
        border: 1px solid #ffd700;
    }

    /* Styling cho payment method selection */
    .payment-method {
        transition: all 0.3s ease;
    }
    
    .payment-method:hover {
        border-color: #667eea !important;
        background-color: #f8f9ff;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
    }
    
    .payment-method.active {
        border-color: #667eea !important;
        background-color: #f0f3ff;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
    }
    
    .payment-method .form-check-input:checked ~ .form-check-label {
        color: #667eea;
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