<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory'; // Su dung ten bang tuong ung la 'inventory'

    protected $primaryKey = 'inventory_id'; // Su dung khoa chinh la 'inventory_id'

    const UPDATED_AT = 'updated_at'; // Su dung cot 'updated_at' de theo doi thoi gian cap nhat

    const CREATED_AT = null; // Khong su dung cot 'created_at'

    protected $fillable = [ // Cac cot co the gan gia tri hang loat
        'product_id',
        'stock_in',
        'stock_out',
        'current_stock',
    ];

    public function product() // Quan he voi model Product
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}
