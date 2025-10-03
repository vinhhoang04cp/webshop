<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        'sort_order',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function details()
    {
        return $this->hasOne(ProductDetail::class, 'product_id', 'product_id');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class, 'product_id', 'product_id');
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class, 'product_id', 'product_id');
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class, 'product_id', 'product_id');
    }

    /**
     * DEPRECATED: Reorder product IDs to ensure sequential numbering (1, 2, 3, ...)
     * WARNING: This method is DANGEROUS and can cause data loss!
     * It disables foreign key checks and manipulates primary keys directly.
     * 
     * RECOMMENDATION: Remove this method entirely. Laravel auto-increment IDs
     * don't need to be sequential. If you need sequential display numbers,
     * use a separate 'display_order' column instead.
     * 
     * This method will be called after create, update, or delete operations
     */
    public static function reorderIds()
    {
        // DISABLED FOR SAFETY - this method is too dangerous
        \Log::warning('reorderIds() method called but disabled for safety. Consider removing this method entirely.');
        return;
        
        // If you really need sequential IDs, consider this safer alternative:
        // 1. Add a 'display_order' column to products table
        // 2. Update display_order instead of primary key
        // 3. Use display_order for sorting/display purposes
        // 4. Never modify primary keys of existing records
    }
}
