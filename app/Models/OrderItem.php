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

    public function order() // quan he 1-1 voi Order
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function product() // quan he 1-1 voi Product
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function productDetail() // quan he 1-1 voi ProductDetail
    {
        return $this->belongsTo(ProductDetail::class, 'detail_id', 'detail_id');
    }

    public function getTotalPriceAttribute() // Ham lay tong gia tri
    {
        return $this->quantity * $this->price; // tinh tong gia tri bang cach nhan so luong voi gia
    }

    public static function reorderIds() // Ham lay danh sach id don hang theo thu tu giam dan
    {
        return self::orderBy('order_id', 'desc')->pluck('order_id')->unique()->toArray();
    }
}
