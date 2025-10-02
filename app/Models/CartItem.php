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
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class, 'cart_id', 'cart_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function price()
    {
        return $this->product ? $this->product->price : 0;
    }

    public function totalPrice()
    {
        return $this->quantity * $this->price();
    }

    protected $appends = ['price', 'total_price'];

    protected $casts = [
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function getPriceAttribute()
    {
        return $this->price();
    }

    public function getTotalPriceAttribute()
    {
        return $this->totalPrice();
    }
}
