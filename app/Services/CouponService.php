<?php

namespace App\Services;

use App\Exceptions\Coupon\CouponExpiredException;
use App\Exceptions\Coupon\CouponInactiveException;
use App\Exceptions\Coupon\CouponNotYetActiveException;
use App\Exceptions\Coupon\CouponUsageLimitExceededException;
use App\Exceptions\Coupon\MinimumOrderAmountNotMetException;
use App\Models\Coupon;
use App\Models\Product;

class CouponService
{
    /**
     * Lấy danh sách coupons với tìm kiếm
     */
    public function getCouponsForAdmin(array $filters = [], int $perPage = 15)
    {
        $query = Coupon::with('product');

        // Tìm kiếm theo mã coupon
        if (! empty($filters['search'])) {
            $query->where('code', 'like', '%'.$filters['search'].'%');
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Lấy chi tiết coupon
     */
    public function getCouponDetail($couponId)
    {
        return Coupon::with('product')->findOrFail($couponId);
    }

    /**
     * Lấy coupon theo ID
     */
    public function getCouponById($couponId)
    {
        return Coupon::findOrFail($couponId);
    }

    /**
     * Lấy danh sách products cho dropdown
     */
    public function getProductsForDropdown()
    {
        return Product::orderBy('name')->get();
    }

    /**
     * Tạo coupon mới
     */
    public function createCoupon(array $data)
    {
        $coupon = Coupon::create([
            'code' => strtoupper($data['code']),
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'product_id' => $data['product_id'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        // Áp dụng discount cho product nếu active
        if ($coupon->is_active && $coupon->product_id) {
            $this->applyDiscountToProduct($coupon);
        }

        return $coupon;
    }

    /**
     * Cập nhật coupon
     */
    public function updateCoupon($couponId, array $data)
    {
        $coupon = Coupon::findOrFail($couponId);
        $oldProductId = $coupon->product_id;
        $oldIsActive = $coupon->is_active;

        $coupon->update([
            'code' => strtoupper($data['code']),
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'product_id' => $data['product_id'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        // Áp dụng discount nếu active
        if ($coupon->is_active && $coupon->product_id) {
            $this->applyDiscountToProduct($coupon);
        }

        return $coupon;
    }

    /**
     * Xóa coupon
     */
    public function deleteCoupon($couponId)
    {
        $coupon = Coupon::findOrFail($couponId);

        // Khôi phục giá sản phẩm nếu coupon đang active
        if ($coupon->is_active && $coupon->product_id) {
            $this->restoreProductPrice($coupon->product_id);
        }

        $coupon->delete();

        return true;
    }

    /**
     * Bật/tắt trạng thái coupon
     */
    public function toggleCouponStatus($couponId)
    {
        $coupon = Coupon::findOrFail($couponId);

        // Khôi phục giá nếu đang tắt coupon
        if ($coupon->is_active && $coupon->product_id) {
            $this->restoreProductPrice($coupon->product_id);
        }

        $coupon->update(['is_active' => ! $coupon->is_active]);

        // Áp dụng discount nếu bật coupon
        if ($coupon->is_active && $coupon->product_id) {
            $this->applyDiscountToProduct($coupon);
        }

        return $coupon;
    }

    /**
     * Áp dụng discount vào product
     */
    protected function applyDiscountToProduct($coupon)
    {
        if (! $coupon->product_id) {
            return;
        }

        $product = Product::find($coupon->product_id);
        if (! $product) {
            return;
        }

        // Lưu giá gốc nếu chưa có
        if ($product->original_price === null) {
            $product->original_price = $product->price;
        }

        // Tính giá sau giảm
        $discountedPrice = $product->original_price - $coupon->calculateDiscount($product->original_price);

        $product->price = max(0, $discountedPrice);
        $product->save();
    }

    /**
     * Khôi phục giá gốc của product
     */
    protected function restoreProductPrice($productId)
    {
        $product = Product::find($productId);
        if (! $product || $product->original_price === null) {
            return;
        }

        $product->price = $product->original_price;
        $product->original_price = null;
        $product->save();
    }

    /**
     * Get coupons with filters (for API and Web)
     */
    public function getCoupons(array $filters = [], int $perPage = 15)
    {
        $query = Coupon::with('product');

        if (isset($filters['code'])) {
            $query->where('code', 'LIKE', '%'.$filters['code'].'%');
        }

        if (isset($filters['description'])) {
            $query->where('description', 'LIKE', '%'.$filters['description'].'%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (isset($filters['discount_type'])) {
            $query->where('discount_type', $filters['discount_type']);
        }

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Find coupon by ID (nullable) with optional relationships
     */
    public function findCoupon($couponId, $withRelations = true)
    {
        $query = Coupon::query();

        if ($withRelations) {
            $query->with('product');
        }

        return $query->find($couponId);
    }

    /**
     * Create coupon and return fresh instance
     */
    public function createCouponWithFresh(array $data)
    {
        $coupon = $this->createCoupon($data);

        return $coupon->fresh();
    }

    /**
     * Update coupon and return fresh instance
     */
    public function updateCouponWithFresh($couponId, array $data)
    {
        $coupon = $this->updateCoupon($couponId, $data);

        return $coupon->fresh();
    }

    /**
     * Create coupon with all fields (for API)
     */
    public function createCouponFull(array $data)
    {
        $coupon = Coupon::create([
            'code' => strtoupper($data['code']),
            'name' => $data['name'] ?? null,
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'max_discount_amount' => $data['max_discount_amount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        if ($coupon->is_active && $coupon->product_id) {
            $this->applyDiscountToProduct($coupon);
        }

        return $coupon->fresh();
    }

    /**
     * Update coupon with all fields (for API)
     */
    public function updateCouponFull($couponId, array $data)
    {
        $coupon = Coupon::findOrFail($couponId);
        $oldProductId = $coupon->product_id;
        $oldIsActive = $coupon->is_active;

        $coupon->update([
            'code' => strtoupper($data['code']),
            'name' => $data['name'] ?? null,
            'discount_type' => $data['discount_type'],
            'discount_value' => $data['discount_value'],
            'min_order_amount' => $data['min_order_amount'] ?? null,
            'max_discount_amount' => $data['max_discount_amount'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'product_id' => $data['product_id'] ?? null,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'is_active' => $data['is_active'] ?? false,
        ]);

        if ($oldIsActive && $oldProductId && ($oldProductId != $coupon->product_id || ! $coupon->is_active)) {
            $this->restoreProductPrice($oldProductId);
        }

        if ($coupon->is_active && $coupon->product_id) {
            $this->applyDiscountToProduct($coupon);
        }

        return $coupon->fresh();
    }

    /**
     * ===================================================================
     * BUSINESS LOGIC METHODS (Moved from Coupon Model)
     * ===================================================================
     */

    /**
     * Kiểm tra tính hợp lệ của coupon
     *
     * @param  float  $orderAmount
     * @return array ['valid' => bool, 'message' => string]
     */
    public function isValid(Coupon $coupon, $orderAmount = 0)
    {
        $now = now();

        // Kiểm tra trạng thái hoạt động
        if (! $coupon->is_active) {
            return ['valid' => false, 'message' => 'Mã giảm giá không còn hoạt động'];
        }

        // Kiểm tra thời gian
        if ($now < $coupon->start_date) {
            return ['valid' => false, 'message' => 'Mã giảm giá chưa có hiệu lực'];
        }

        if ($now > $coupon->end_date) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn'];
        }

        // Kiểm tra số lần sử dụng
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng'];
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($orderAmount > 0 && $orderAmount < $coupon->min_order_amount) {
            return [
                'valid' => false,
                'message' => 'Đơn hàng chưa đạt giá trị tối thiểu '.number_format($coupon->min_order_amount, 0, ',', '.').' VND',
            ];
        }

        return ['valid' => true, 'message' => 'Mã giảm giá hợp lệ'];
    }

    /**
     * Validate coupon and throw exceptions if invalid
     * (Strict validation version for new code)
     *
     * @param  float  $orderAmount
     * @return void
     *
     * @throws CouponInactiveException
     * @throws CouponNotYetActiveException
     * @throws CouponExpiredException
     * @throws CouponUsageLimitExceededException
     * @throws MinimumOrderAmountNotMetException
     */
    public function validateCoupon(Coupon $coupon, $orderAmount = 0)
    {
        $now = now();

        // Kiểm tra trạng thái hoạt động
        if (! $coupon->is_active) {
            throw new CouponInactiveException($coupon->code);
        }

        // Kiểm tra thời gian
        if ($now < $coupon->start_date) {
            throw new CouponNotYetActiveException(
                $coupon->code,
                $coupon->start_date->format('d/m/Y')
            );
        }

        if ($now > $coupon->end_date) {
            throw new CouponExpiredException($coupon->code);
        }

        // Kiểm tra số lần sử dụng
        if ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit) {
            throw new CouponUsageLimitExceededException($coupon->code);
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($orderAmount > 0 && $orderAmount < $coupon->min_order_amount) {
            throw new MinimumOrderAmountNotMetException($coupon->min_order_amount);
        }
    }

    /**
     * Tính toán số tiền giảm giá
     *
     * @param  float  $price
     * @return float
     */
    public function calculateDiscount(Coupon $coupon, $price)
    {
        if (! $this->isValid($coupon, $price)['valid']) {
            return 0;
        }

        $discount = 0;

        if ($coupon->discount_type === 'percentage') {
            $discount = ($price * $coupon->discount_value) / 100;

            // Áp dụng giảm giá tối đa nếu có
            if ($coupon->max_discount_amount !== null) {
                $discount = min($discount, $coupon->max_discount_amount);
            }
        } else {
            $discount = $coupon->discount_value;
        }

        // Đảm bảo số tiền giảm không vượt quá giá trị đơn hàng
        return min($discount, $price);
    }

    /**
     * Kiểm tra coupon có áp dụng cho sản phẩm cụ thể không
     *
     * @param  int  $productId
     * @return bool
     */
    public function appliesTo(Coupon $coupon, $productId)
    {
        // Nếu product_id null = áp dụng cho tất cả
        if ($coupon->product_id === null) {
            return true;
        }

        // Nếu có product_id = chỉ áp dụng cho sản phẩm đó
        return $coupon->product_id == $productId;
    }

    /**
     * Tìm coupon theo mã code
     *
     * @param  string  $code
     * @return Coupon|null
     */
    public function findByCode($code)
    {
        return Coupon::where('code', strtoupper(trim($code)))->first();
    }

    /**
     * Lấy tất cả coupon hợp lệ cho sản phẩm
     *
     * @param  int  $productId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getValidCouponsForProduct($productId)
    {
        return Coupon::active()
            ->valid()
            ->forProduct($productId)
            ->get();
    }

    /**
     * Lấy coupon tốt nhất cho một giá trị
     *
     * @param  float  $price
     * @param  int|null  $productId
     * @return Coupon|null
     */
    public function getBestCouponForPrice($price, $productId = null)
    {
        $query = Coupon::active()->valid();

        if ($productId) {
            $query->forProduct($productId);
        }

        $coupons = $query->get();

        if ($coupons->isEmpty()) {
            return null;
        }

        // Tìm coupon giảm giá nhiều nhất
        return $coupons->sortByDesc(function ($coupon) use ($price) {
            return $this->calculateDiscount($coupon, $price);
        })->first();
    }
}
