<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories'; // Su dung ten bang tuong ung la 'categories'

    protected $primaryKey = 'category_id'; // Su dung khoa chinh la 'category_id'

    public $timestamps = true; // Su dung cot 'created_at' va 'updated_at' de theo doi thoi gian

    protected $fillable = [ // Cac cot co the gan gia tri hang loat
        'name',
        'description',
    ];

    public function products() // Quan he 1-n voi Product
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id'); // Quan he voi model Product
        // (Product::class: model Product, 'category_id': khoa ngoai trong bang products tham chieu den khoa chinh cua bang categories)
    }
}
