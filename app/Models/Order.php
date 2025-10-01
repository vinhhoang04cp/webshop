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
    ];

    protected $casts = [
        'order_date' => 'datetime',
    ];

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
