<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CartItem extends Model
{
    use HasFactory;

    protected $table = 'cart_items';

    protected $primaryKey = 'cart_item_id';

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    /**
     * Phương thức trợ giúp để lấy giá (từ column price nếu có, hoặc từ product)
     */
    public function price()
    {
        // Nếu có price được lưu trong cart_items, dùng nó; nếu không thì lấy từ product
        return $this->price ?? ($this->product ? $this->product->price : 0);
    }

    /**
     * Phương thức trợ giúp để tính tổng giá
     */
    public function totalPrice()
    {
        return $this->quantity * $this->price();
    }

    protected $appends = ['price', 'total_price'];

    protected $casts = [
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    /**
     * Accessor để lấy giá sản phẩm (trả về từ helper method hoặc column price)
     */
    public function getPriceAttribute()
    {
        // Accessor này chỉ kích hoạt khi truy cập thuộc tính thông qua $cartItem->price
        // và khi không có giá trị trong database
        if (! isset($this->attributes['price']) || $this->attributes['price'] === null) {
            return $this->price();
        }

        return $this->attributes['price'];
    }

    /**
     * Accessor để tính tổng giá
     */
    public function getTotalPriceAttribute()
    {
        return $this->totalPrice();
    }

}
