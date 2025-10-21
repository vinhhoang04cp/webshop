<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $primaryKey = 'product_id';

    protected $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'stock_quantity',
        'image_url',
    ];

    public function category() // quan he 1-1 voi Category
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function details() // quan he 1-1 voi ProductDetail
    {
        return $this->hasOne(ProductDetail::class, 'product_id', 'product_id');
    }

    public function inventory() // quan he 1-1 voi Inventory
    {
        return $this->hasOne(Inventory::class, 'product_id', 'product_id');
    }

    public function orderItems() // quan he 1-n voi OrderItem
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    public function cartItems() // quan he 1-n voi CartItem
    {
        return $this->hasMany(CartItem::class, 'product_id', 'product_id');
    }

    public function ratings() // quan he 1-n voi Rating
    {
        return $this->hasMany(Rating::class, 'product_id', 'product_id');
    }

    // Hàm tính trung bình rating
    public function averageRating()
    {
        return $this->ratings()->avg('rating') ?? 0;
    }

    // Hàm đếm số lượng rating
    public function totalRatings()
    {
        return $this->ratings()->count();
    }
}
