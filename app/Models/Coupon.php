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
        'discount_type',
        'discount_value',
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

    /**
     * Quan hệ với Product (1 coupon thuộc về 1 sản phẩm hoặc null = tất cả)
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Kiểm tra xem coupon có hợp lệ không
     */
    public function isValid()
    {
        $now = Carbon::now();

        // Kiểm tra trạng thái hoạt động
        if (! $this->is_active) {
            return ['valid' => false, 'message' => 'Mã giảm giá không còn hoạt động'];
        }

        // Kiểm tra thời gian
        if ($now < $this->start_date) {
            return ['valid' => false, 'message' => 'Mã giảm giá chưa có hiệu lực'];
        }

        if ($now > $this->end_date) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết hạn'];
        }

        return ['valid' => true, 'message' => 'Mã giảm giá hợp lệ'];
    }

    /**
     * Tính toán số tiền giảm giá cho một giá trị
     */
    public function calculateDiscount($price)
    {
        if (! $this->isValid()['valid']) {
            return 0;
        }

        $discount = 0;

        if ($this->discount_type === 'percentage') {
            $discount = ($price * $this->discount_value) / 100;
        } else {
            $discount = $this->discount_value;
        }

        // Đảm bảo số tiền giảm không vượt quá giá trị
        return min($discount, $price);
    }

    /**
     * Kiểm tra coupon có áp dụng cho sản phẩm cụ thể không
     */
    public function appliesTo($productId)
    {
        // Nếu product_id null = áp dụng cho tất cả
        if ($this->product_id === null) {
            return true;
        }

        // Nếu có product_id = chỉ áp dụng cho sản phẩm đó
        return $this->product_id == $productId;
    }

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
