{{-- 
    Component Checkout Form
    Props: None (sử dụng Auth::user())
--}}

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

    {{-- Chọn phương thức thanh toán --}}
    <div class="mb-4">
        <label class="form-label fw-bold">
            <i class="fas fa-credit-card"></i> Chọn phương thức thanh toán
            <span class="text-danger">*</span>
        </label>
        
        {{-- Thanh toán COD --}}
        <div class="payment-method mb-3" onclick="selectPaymentMethod('cod')">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="payment_cod" value="cod" checked>
                <label class="form-check-label w-100" for="payment_cod">
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

        {{-- Thanh toán VNPay --}}
        <div class="payment-method mb-3" onclick="selectPaymentMethod('vnpay')">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="payment_method" id="payment_vnpay" value="vnpay">
                <label class="form-check-label w-100" for="payment_vnpay">
                    <div class="d-flex align-items-center">
                        <div class="me-3 payment-icon">
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

    <button type="submit" class="btn btn-primary w-100 mb-3 btn-checkout">
        <i class="fas fa-check"></i> <span id="checkout-btn-text">Đặt hàng (COD)</span>
    </button>
</form>

<style>
.payment-method {
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    padding: 15px;
    cursor: pointer;
    transition: all 0.3s;
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

.payment-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #0088cc, #00aaff);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-checkout {
    padding: 12px;
    border-radius: 25px;
    font-size: 1.1rem;
}
</style>

