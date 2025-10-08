<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $table = 'orders';

    protected $primaryKey = 'order_id';

    protected $fillable = [
        'user_id',
        'order_date',
        'total_amount',
        'status',
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];

    // Định nghĩa các trạng thái hợp lệ
    const STATUS_PENDING = 'pending';

    const STATUS_PROCESSING = 'processing';

    const STATUS_SHIPPED = 'shipped';

    const STATUS_DELIVERED = 'delivered';

    const STATUS_CANCELLED = 'cancelled';

    // Định nghĩa workflow chuyển trạng thái hợp lệ
    const STATUS_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_PROCESSING, self::STATUS_CANCELLED],
        self::STATUS_PROCESSING => [self::STATUS_SHIPPED, self::STATUS_CANCELLED],
        self::STATUS_SHIPPED => [self::STATUS_DELIVERED],
        self::STATUS_DELIVERED => [],
        self::STATUS_CANCELLED => [],
    ];

    /**
     * Kiểm tra xem có thể chuyển sang trạng thái mới không
     */
    public function canTransitionTo(string $newStatus): bool
    {
        $currentStatus = $this->status ?? self::STATUS_PENDING;

        if (! isset(self::STATUS_TRANSITIONS[$currentStatus])) {
            return false;
        }

        return in_array($newStatus, self::STATUS_TRANSITIONS[$currentStatus]);
    }

    /**
     * Chuyển sang trạng thái mới nếu hợp lệ
     */
    public function transitionTo(string $newStatus): bool
    {
        if (! $this->canTransitionTo($newStatus)) {
            return false;
        }

        $this->status = $newStatus;

        return $this->save();
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id')
            ->withPivot('quantity', 'price')
            ->withTimestamps();
    }

    public function getProductCountAttribute()
    {
        return $this->items()->count();
    }

    public function getTotalQuantityAttribute()
    {
        return $this->items()->sum('quantity');
    }

    // Removed getTotalAmountAttribute to avoid conflict with fillable total_amount field

    public static function reorderIds()
    {
        // Tắt tạm thời kiểm tra foreign key để có thể cập nhật ID
        \DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Lấy tất cả orders theo thứ tự ID hiện tại
        $orders = self::orderBy('order_id')->get();

        // Cập nhật lại ID tuần tự bắt đầu từ 1
        $newId = 1;
        foreach ($orders as $order) {
            $order->order_id = $newId++;
            $order->save();
        }

        // Bật lại kiểm tra foreign key
        \DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }
}
