{{-- 
    Component Product Pricing Detail
    Props:
    - $product: Object sản phẩm (bắt buộc)
--}}

@php
    // Nếu sản phẩm có original_price, nghĩa là đã được áp dụng coupon từ backend
    $hasAppliedCoupon = $product->original_price !== null;
    
    if ($hasAppliedCoupon) {
        // Sử dụng giá đã được cập nhật trong database
        $originalPrice = $product->original_price;
        $currentPrice = $product->price;
        $savedAmount = $originalPrice - $currentPrice;
        
        // Lấy thông tin coupon đang active cho sản phẩm
        $bestCoupon = \App\Models\Coupon::where('product_id', $product->product_id)
            ->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();
    } else {
        // Logic cũ: Tính toán động cho coupon chung (product_id = null)
        $bestCoupon = $product->getBestCoupon();
        $currentPrice = $product->price;
        
        if ($bestCoupon) {
            $discountedPrice = $product->getDiscountedPrice();
            $originalPrice = $product->price;
            $currentPrice = $discountedPrice;
            $savedAmount = $originalPrice - $currentPrice;
        } else {
            $originalPrice = null;
            $savedAmount = 0;
        }
    }
    
    $hasCoupon = $bestCoupon !== null;
@endphp

<div class="mb-4">
    @if($hasCoupon)
        {{-- Có coupon giảm giá --}}
        <div class="mb-3 p-3 bg-warning bg-opacity-10 border border-warning rounded">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-ticket-alt text-warning fa-lg me-2"></i>
                <span class="badge bg-warning text-dark px-3 py-2" style="font-size: 1.1em;">
                    <strong>{{ $bestCoupon->code }}</strong> - Giảm {{ $bestCoupon->discount_display }}
                </span>
            </div>
            <div class="text-muted small">
                <i class="fas fa-clock me-1"></i>
                Có hiệu lực đến {{ $bestCoupon->end_date->format('d/m/Y') }}
            </div>
        </div>
        
        <div class="d-flex align-items-baseline gap-3">
            <h2 class="text-danger mb-0" style="font-size: 2.5rem; font-weight: 700;">
                {{ number_format($currentPrice, 0, ',', '.') }}₫
            </h2>
            @if($originalPrice && $savedAmount > 0)
                <span class="text-muted text-decoration-line-through" style="font-size: 1.5rem;">
                    {{ number_format($originalPrice, 0, ',', '.') }}₫
                </span>
                <span class="badge bg-danger" style="font-size: 0.9rem;">
                    Tiết kiệm {{ number_format($savedAmount, 0, ',', '.') }}₫
                </span>
            @endif
        </div>
    @else
        <h2 class="text-primary mb-0" style="font-size: 2.5rem; font-weight: 700;">
            {{ number_format($product->price, 0, ',', '.') }}₫
        </h2>
    @endif
</div>

