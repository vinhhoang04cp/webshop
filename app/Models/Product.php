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
     * Reorder product IDs to ensure sequential numbering (1, 2, 3, ...)
     * This method will be called after create, update, or delete operations
     */
    public static function reorderIds()
    {
        // Tắt tạm thời kiểm tra foreign key để có thể cập nhật ID
        DB::statement('SET FOREIGN_KEY_CHECKS = 0');

        // Lấy tất cả products theo thứ tự ID hiện tại
        $products = self::orderBy('product_id', 'asc')->get();

        if ($products->isEmpty()) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 1');

            return;
        }

        // Sử dụng một giá trị offset lớn để tránh trùng lặp
        $offset = 2000000;

        // Đầu tiên, cập nhật tất cả IDs thành giá trị lớn để tránh trùng lặp
        foreach ($products as $index => $product) {
            $tempId = $offset + $index + 1;
            DB::table('products')
                ->where('product_id', $product->product_id)
                ->update(['product_id' => $tempId]);

            // Cập nhật foreign key references
            DB::table('product_details')
                ->where('product_id', $product->product_id)
                ->update(['product_id' => $tempId]);

            DB::table('inventory')
                ->where('product_id', $product->product_id)
                ->update(['product_id' => $tempId]);

            DB::table('order_items')
                ->where('product_id', $product->product_id)
                ->update(['product_id' => $tempId]);

            DB::table('cart_items')
                ->where('product_id', $product->product_id)
                ->update(['product_id' => $tempId]);
        }

        // Sau đó cập nhật thành giá trị cuối cùng (1, 2, 3, ...)
        foreach ($products as $index => $product) {
            $newId = $index + 1;
            $tempId = $offset + $index + 1;

            DB::table('products')
                ->where('product_id', $tempId)
                ->update(['product_id' => $newId]);

            // Cập nhật foreign key references
            DB::table('product_details')
                ->where('product_id', $tempId)
                ->update(['product_id' => $newId]);

            DB::table('inventory')
                ->where('product_id', $tempId)
                ->update(['product_id' => $newId]);

            DB::table('order_items')
                ->where('product_id', $tempId)
                ->update(['product_id' => $newId]);

            DB::table('cart_items')
                ->where('product_id', $tempId)
                ->update(['product_id' => $newId]);
        }

        // Reset AUTO_INCREMENT của bảng để ID tiếp theo sẽ đúng
        $maxId = $products->count();
        DB::statement('ALTER TABLE products AUTO_INCREMENT = '.($maxId + 1));

        // Bật lại kiểm tra foreign key
        DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    }

    protected static function booted()
    {
        static::created(function ($product) {
            self::reorderIds();
        });

        static::updated(function ($product) {
            self::reorderIds();
        });

        static::deleted(function ($product) {
            self::reorderIds();
        });
    } 
}
