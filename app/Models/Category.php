<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Category extends Model
{
    use HasFactory;

    protected $table = 'categories';

    protected $primaryKey = 'category_id';

    public $timestamps = true;

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * DEPRECATED & DISABLED: Reorder category IDs to ensure sequential numbering (1, 2, 3, ...).
     * WARNING: This method is DANGEROUS and can cause data loss!
     * This method will be called after create, update, or delete operations.
     */
    public static function reorderIds()
    {
        \Log::warning('Category::reorderIds() called but disabled for safety. Consider removing this method entirely.');
        return;
        
        // DISABLED FOR SAFETY - manipulating primary keys is dangerous
        // If you need sequential display numbers, use a 'display_order' column instead
    }

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }
}
