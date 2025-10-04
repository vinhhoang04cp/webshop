<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductDetail extends Model
{
    use HasFactory;

    protected $table = 'product_details';

    protected $primaryKey = 'detail_id';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'size',
        'color',
        'material',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'detail_id', 'detail_id');
    }

    public function stocks()
    {
        return $this->hasMany(Stock::class, 'detail_id', 'detail_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'detail_id', 'detail_id');
    }
}
