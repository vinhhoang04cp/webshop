<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $table = 'coupons';

    protected $primaryKey = 'coupon_id';

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'product_id',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * ===================================================================
     * DEPRECATED METHODS - Use CouponService instead
     * ===================================================================
     * These methods are kept for backward compatibility
     * but should use CouponService for new code
     */

    /**
     * @deprecated Use CouponService::isValid() instead
     */
    public function isValid($orderAmount = 0)
    {
        return app(\App\Services\CouponService::class)->isValid($this, $orderAmount);
    }

    /**
     * @deprecated Use CouponService::calculateDiscount() instead
     */
    public function calculateDiscount($price)
    {
        return app(\App\Services\CouponService::class)->calculateDiscount($this, $price);
    }

    /**
     * @deprecated Use CouponService::appliesTo() instead
     */
    public function appliesTo($productId)
    {
        return app(\App\Services\CouponService::class)->appliesTo($this, $productId);
    }

    /**
     * ===================================================================
     * QUERY SCOPES (These are OK to keep in Model)
     * ===================================================================
     */

    /**
     * Scope để lấy coupon đang hoạt động
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope để lấy coupon trong thời gian hiệu lực
     */
    public function scopeValid($query)
    {
        $now = Carbon::now();

        return $query->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now);
    }

    /**
     * Scope để lấy coupon cho sản phẩm cụ thể hoặc tất cả
     */
    public function scopeForProduct($query, $productId)
    {
        return $query->where(function ($q) use ($productId) {
            $q->whereNull('product_id')
                ->orWhere('product_id', $productId);
        });
    }

    /**
     * Accessor để hiển thị định dạng discount
     */
    public function getDiscountDisplayAttribute()
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value.'%';
        } else {
            return number_format($this->discount_value, 0, ',', '.').' VND';
        }
    }

    /**
     * Accessor để hiển thị trạng thái
     */
    public function getStatusDisplayAttribute()
    {
        $now = Carbon::now();

        if (! $this->is_active) {
            return 'Không hoạt động';
        }

        if ($now < $this->start_date) {
            return 'Chưa bắt đầu';
        }

        if ($now > $this->end_date) {
            return 'Đã hết hạn';
        }

        return 'Đang hoạt động';
    }

    /**
     * Accessor để hiển thị phạm vi áp dụng
     */
    public function getScopeDisplayAttribute()
    {
        if ($this->product_id === null) {
            return 'Tất cả sản phẩm';
        }

        return $this->product ? $this->product->name : 'Sản phẩm #'.$this->product_id;
    }
}
