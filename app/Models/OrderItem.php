<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $table = 'order_items';

    protected $primaryKey = 'order_item_id';

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function productDetail()
    {
        return $this->belongsTo(ProductDetail::class, 'detail_id', 'detail_id');
    }

    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->price;
    }

    public static function reorderIds()
    {
        return self::orderBy('order_id', 'desc')->pluck('order_id')->unique()->toArray();
    }
}
