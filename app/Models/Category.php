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
     * Reorder category IDs to ensure sequential numbering (1, 2, 3, ...).
     * This method will be called after create, update, or delete operations.
     */
    public static function reorderIds()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        $categories = self::orderBy('category_id', 'asc')->get();

        if ($categories->isEmpty()) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            return;
        }

        $offset = 1000000;

        foreach ($categories as $index => $category) {
            $tempId = $offset + $index + 1;
            DB::table('categories')
                ->where('category_id', $category->category_id)
                ->update(['category_id' => $tempId]);

            DB::table('products')
                ->where('category_id', $category->category_id)
                ->update(['category_id' => $tempId]);
        }

        foreach ($categories as $index => $category) {
            $newId = $index + 1;
            $tempId = $offset + $index + 1;

            DB::table('categories')
                ->where('category_id', $tempId)
                ->update(['category_id' => $newId]);

            DB::table('products')
                ->where('category_id', $tempId)
                ->update(['category_id' => $newId]);
        }

        $maxId = $categories->count();
        DB::statement('ALTER TABLE categories AUTO_INCREMENT = '.($maxId + 1));

        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    /**
     * Get the products for the category.
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id', 'category_id');
    }
}
