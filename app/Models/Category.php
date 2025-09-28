<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories'; // Ten bang trong CSDL

    protected $primaryKey = 'category_id';   // Khoa chinh cua bang

    public $timestamps = true; // Tu dong quan ly created_at va updated_at

    protected $fillable = [
        'name',
        'description',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id'); // Moi quan he 1-n voi Product
    }
}
