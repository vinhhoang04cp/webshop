<?php

namespace App\Services;

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
     * Get coupons with filters (for API)
     */
    public function getCoupons(array $filters = [], int $perPage = 15)
    {
        $query = Coupon::query();

        if (isset($filters['code'])) {
            $query->where('code', 'LIKE', '%'.$filters['code'].'%');
        }

        if (isset($filters['description'])) {
            $query->where('description', 'LIKE', '%'.$filters['description'].'%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Find coupon by ID (nullable)
     */
    public function findCoupon($couponId)
    {
        return Coupon::find($couponId);
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
}
