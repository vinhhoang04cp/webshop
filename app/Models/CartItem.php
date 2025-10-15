<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
     * Phương thức trợ giúp để lấy giá hiệu quả (từ column price nếu có, hoặc từ product)
     */
    public function getEffectivePrice()
    {
        // Nếu có price được lưu trong cart_items, dùng nó; nếu không thì lấy từ product
        return $this->attributes['price'] ?? ($this->product ? $this->product->price : 0);
    }

    /**
     * Phương thức trợ giúp để tính tổng giá
     */
    public function getItemTotal()
    {
        return $this->quantity * $this->getEffectivePrice();
    }

    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Accessor để tính tổng giá của item này
     */
    public function getTotalPriceAttribute()
    {
        return $this->getItemTotal();
    }
}
