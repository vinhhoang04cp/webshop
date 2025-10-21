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
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
    ];

    /**
     * Kiểm tra xem coupon có hợp lệ không
     */
    public function isValid($orderAmount = 0)
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

        // Kiểm tra giới hạn sử dụng
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'Mã giảm giá đã hết lượt sử dụng'];
        }

        // Kiểm tra giá trị đơn hàng tối thiểu
        if ($orderAmount < $this->min_order_amount) {
            return ['valid' => false, 'message' => 'Đơn hàng chưa đạt giá trị tối thiểu '.number_format($this->min_order_amount, 0, ',', '.').' VND'];
        }

        return ['valid' => true, 'message' => 'Mã giảm giá hợp lệ'];
    }

    /**
     * Tính toán số tiền giảm giá
     */
    public function calculateDiscount($orderAmount)
    {
        if (! $this->isValid($orderAmount)['valid']) {
            return 0;
        }

        $discount = 0;

        if ($this->discount_type === 'percentage') {
            $discount = ($orderAmount * $this->discount_value) / 100;

            // Áp dụng giới hạn giảm tối đa nếu có
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                $discount = $this->max_discount_amount;
            }
        } else {
            $discount = $this->discount_value;
        }

        // Đảm bảo số tiền giảm không vượt quá tổng đơn hàng
        return min($discount, $orderAmount);
    }

    /**
     * Tăng số lần sử dụng coupon
     */
    public function incrementUsage()
    {
        $this->increment('used_count');
    }

    /**
     * Giảm số lần sử dụng coupon (khi hủy đơn hàng)
     */
    public function decrementUsage()
    {
        if ($this->used_count > 0) {
            $this->decrement('used_count');
        }
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
     * Scope để lấy coupon còn lượt sử dụng
     */
    public function scopeAvailable($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')
                ->orWhereRaw('used_count < usage_limit');
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

        if ($this->usage_limit && $this->used_count >= $this->usage_limit) {
            return 'Hết lượt sử dụng';
        }

        return 'Đang hoạt động';
    }
}
